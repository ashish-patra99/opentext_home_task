<?php

namespace App\Controller;

use App\Entity\ApiJobs;
use App\Entity\JobFile;
use App\Entity\JobNotification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use App\Service\DebrickedApiService;
use App\Service\FileUploaderService;
use Doctrine\ORM\EntityManagerInterface;
use LDAP\Result;

class DebrickedApiController extends AbstractController
{
    /**
     * @debrickedAPIService 
     */
    private $debrickedAPIService;
    private $fileUploader;
    private $jsonResponse;
    private $entityManager;
    private $notifier;

    public function __construct(DebrickedApiService $debrickedAPIService, FileUploaderService $fileUploader,EntityManagerInterface $entityManager, NotifierInterface $notifier)
    {
        $this->debrickedAPIService = $debrickedAPIService;
        $this->fileUploader = $fileUploader;
        $this->entityManager = $entityManager;
        $this->notifier = $notifier;
    }

    #[Route('/api/jwt', name: 'app_debricked_api_jwt')]
    public function getJWT(): JsonResponse
    {
        try {

            $jwt = $this->debrickedAPIService->authenticate();

            if(!$jwt){
                throw new \Exception('JWT Not retrieved');
            }
            return new JsonResponse(['jwt' => $jwt]);
        }catch (\Exception $e) {
            return new JsonResponse(['error'=>$e->getMessage(),'Statuscode'=>Response::HTTP_NOT_FOUND]);
        }
    }
    
    #[Route('/api/scanFiles', name: 'app_scan_files', methods:['POST'])]
    public function scanFiles(Request $request)
    {
        try {
            $apiJob = new ApiJobs();
            $jobFile = new JobFile();
            $apiJob->setRequestStartedAt(new \DateTimeImmutable());
            $apiJob->setApiJobType('uploadFiles');
            $apiJob->setApiJobEndPoint($_ENV['DEBRICKED_API_URL'].'1.0/open/uploads/dependencies/files');

            $files= $request->files->get('uploadedFiles');
            if(empty($files)) {
                throw new \Exception('No Files Uploaded');
            }
            if ($files) {
                $fileNames = $this->fileUploader->upload($files);
                $uploadResults = '';
                $concludeFileUpload = '';
                    foreach ($fileNames as $file) {
                        $jobFile = new JobFile();
                        $uploadResults = $this->debrickedAPIService->uploadDependencyFiles($file);
                        $jobFile->setFileName(basename($file));
                        $jobFile->setScannedAt(new \DateTimeImmutable());
                        $jobFile->setCiUploadId($uploadResults);
                        $jobFile->setStatus('in_progress');
                        $apiJob->addJobFile($jobFile);
                    }

                    $apiJob->setDebrickedUploadId($uploadResults);
                    $apiJob->setNotificationSent(0);
                    
                    if(!empty($uploadResults)){
                        $apiJob->setRequestCompletedAt(new \DateTimeImmutable());
                        /*another record to know if files move to queue for sacn*/
                        $apiJobConclude = new ApiJobs();
                        $apiJobConclude->setRequestStartedAt(new \DateTimeImmutable());
                        $apiJobConclude->setApiJobType('queueFiles');
                        $apiJobConclude->setApiJobEndPoint($_ENV['DEBRICKED_API_URL'].'1.0/open/finishes/dependencies/files/uploads');
                        $apiJobConclude->setDebrickedUploadId($uploadResults);
                        $apiJobConclude->setNotificationSent(0);
                        $apiJobConclude->setScanStatus('in_progress');
                        $concludeFileUpload = $this->debrickedAPIService->concludeDependencies($uploadResults);
                    }
                    if(!empty($concludeFileUpload)){
                        $apiJobConclude->setRequestCompletedAt(new \DateTimeImmutable());
                        $apiJobConclude->setScanStatus('completed');
                        $apiJob->setScanStatus('in_progress');
                        $this->entityManager->persist($apiJob);
                        $this->entityManager->persist($apiJobConclude);
                        $this->entityManager->flush();
                    }
                    /* Send file upload id to scan for records and notify users if pending*/
                    return $this->findPendingJobstoScan($uploadResults);
                // $this->jsonResponse=new JsonResponse(['Files uploaded'=>rtrim(implode(', ', $fileNames),','),'DebrickUploadId'=> $uploadResults,'DebrickRepositoryId' => $concludeFileUpload]);
                // return $this->jsonResponse;
            }
        }catch (\Exception $e) {

            $this->jsonResponse=new JsonResponse(['error'=>$e->getMessage()]);
            $this->jsonResponse->setStatusCode(400,Response::HTTP_BAD_REQUEST);
            return $this->jsonResponse;
        }
    }

   
    /**
     * Fction to start checking status for files,validate and notify
     *
     * @param string $uploadId=null
     * 
     * @return JsonResponse
     * 
     */
    #[Route('/api/scanPending', name: 'app_scan_pending', methods:['GET'])]
    public function findPendingJobstoScan(string $uploadId=null):JsonResponse
    {
        try {
            $results=[];
            
             if($uploadId !=null){
                $statusJobs= $this->entityManager->getRepository(ApiJobs::class)->findOneByUploadId($uploadId);
             }else{
                $statusJobs= $this->entityManager->getRepository(ApiJobs::class)->findByJobStatus('in_progress');
             }
             
            if (!$statusJobs) {
                return new JsonResponse(['message'=>"No pending Jobs to scan"]);
            }
            foreach ($statusJobs as $job) {
                        $apiJobScan = new ApiJobs();
                        $apiJobScan->setRequestStartedAt(new \DateTimeImmutable());
                        $apiJobScan->setApiJobType('checkStatus');
                        $apiJobScan->setApiJobEndPoint($_ENV['DEBRICKED_API_URL'].'1.0/open/ci/upload/status?ciUploadId='. $job->getDebrickedUploadId());
                        $apiJobScan->setDebrickedUploadId($job->getDebrickedUploadId());
                        $apiJobScan->setNotificationSent(0);
                        $apiJobScan->setScanStatus('in_progress');
                        $this->entityManager->persist($apiJobScan);
                $results[] = $this->debrickedAPIService->getFileScanStatus($job->getDebrickedUploadId());
                $apiJobScan->setRequestCompletedAt(new \DateTimeImmutable());
                $apiJobScan->setScanStatus('completed');
            }
           
            if(count($results) >0){
                
                foreach($results as $jobstatus){
                    $apijob = $this->entityManager->getRepository(ApiJobs::class)->findOneByDebrickedUploadId($jobstatus['ciUploadId']);
                    if (!$apijob) {
                        throw $this->createNotFoundException(
                            'No Job found for id '.$jobstatus['ciUploadId']
                        );
                    }
                    $jobFile = $this->entityManager->getRepository(JobFile::class)->findByJobId($apijob->getId());
                    //$jobFile = $apijob->getFiles();
                    if (!$jobFile) {
                        throw $this->createNotFoundException(
                            'No Files found for Job id '.$apijob->getId()
                        );
                    }
                    
                    
                    if(($jobstatus['progress']) == 100){
                        $apijob->setScanStatus('completed');
                        foreach($jobFile as $file) {
                            // I don't know if you need this line
                            $file->setStatus('completed');
                            $this->entityManager->persist($file);
                        }
                        $this->entityManager->persist($apijob);
                        $this->entityManager->persist($apiJobScan);
                    }
                    /*verifyRule and send notification*/
                    $this->checkRulesAndNotify($jobstatus,$apijob);
                    

                }
                $this->entityManager->flush();
            }
            $this->jsonResponse=new JsonResponse(['data'=>$results]);
            $this->jsonResponse->setStatusCode(200,Response::HTTP_OK);
            return  $this->jsonResponse;

       }catch (\Exception $e) {

        $this->jsonResponse=new JsonResponse(['error'=>$e->getMessage()]);
        $this->jsonResponse->setStatusCode(500,Response::HTTP_INTERNAL_SERVER_ERROR);
        return $this->jsonResponse;
        } 
    }
    
    
    /**
     * verify customRule and send Notification to user 
     *
     * @param mixed $scanStatus
     * 
     * @return [type]
     * 
     */
    private function checkRulesAndNotify($scanStatus,ApiJobs $jobDetails): bool
    {
        $notificationStatus = false;
        $notificationImportance = '';
        /*setting content for Notification for specific Job'*/
        $subject = 'Vulnerability Alert ! JobId-'.$jobDetails->getDebrickedUploadId();
        $content='Job-'.$jobDetails->getDebrickedUploadId().' is completed with '.$scanStatus['vulnerabilitiesFound'].' vulnerabilities,please look int the report logging into debricked account';

        $customRules = ['vulnerabilityAlertCount'=> ['urgent'=>15,'high'=>10], 'status'=> 100];
        
        $notification = new JobNotification();
        $notification->setApiJobId($jobDetails);

        if($scanStatus['progress'] != $customRules['status']) {
            $subject = 'Scan In progress for Job:-'.$jobDetails->getDebrickedUploadId();
            $content='Scan In progress for Job:-'.$jobDetails->getDebrickedUploadId().' we will notify you once completed' ;
            $notificationImportance = Notification::IMPORTANCE_LOW;
            $notification->setJobType('email');
            $this->sendNotification($subject,$content,$notificationImportance);
        } else{
            $notification->setJobType('slack|email');
            $vulnerabilitiesCount = $scanStatus['vulnerabilitiesFound']?? 0;
            
            switch($vulnerabilitiesCount){
                case ($vulnerabilitiesCount >= $customRules['vulnerabilityAlertCount']['urgent']):
                        $notificationImportance = Notification::IMPORTANCE_URGENT;
                    break;
                case ($vulnerabilitiesCount >= $customRules['vulnerabilityAlertCount']['high'] && $vulnerabilitiesCount < $customRules['vulnerabilityAlertCount']['urgent']):
                    $notificationImportance = Notification::IMPORTANCE_HIGH;
                    break;
                default:
                    $notificationImportance = Notification::IMPORTANCE_LOW;
            }
            if ($vulnerabilitiesCount > $customRules['vulnerabilityAlertCount']['high']) {
                $this->sendNotification($subject,$content,$notificationImportance);
            }

        }
        $notification->setRecepient($_ENV['ALERT_RECEPIENT_EMAIL']);
        $notification->setMessage("Subject:-".$subject." / Content:-".$content);
        $notification->setSentAt(new \DateTimeImmutable());
        $notification->setStatus(1);

        $jobDetails->setNotificationSent(1);
        $this->entityManager->persist($notification);
        
       
        $notificationStatus = true;

        return  $notificationStatus;

    }

    /**
     * function to send notification to user with job status
     *
     * @param string $channel
     * @param string $content
     * 
     * @return Response
     * 
     */
    private function sendNotification(string $subject,string $content,$importance): bool
    {
        try{
                if($_ENV['SEND_NOTIFICATION']){
                $notification = (new Notification($subject))
                ->content($content)
                ->importance($importance);
                // The receiver of the Notification
                $recipient = new Recipient($_ENV['ALERT_RECEPIENT_EMAIL']);

                $this->notifier->send($notification, $recipient);

            }
      
        }catch (TransportExceptionInterface  $e) {

            echo 'Failed to send notification: '.$e->getMessage();
            
        } 
        return true;

    }
}
