<?php

namespace App\Repository;

use App\Entity\ApiJobs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApiJobs>
 */
class ApiJobsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiJobs::class);
    }

        /**
         * @return ApiJobs[] Returns an array of ApiJobs objects
         */
       public function findByJobStatus(string $status): array
       {
           return $this->createQueryBuilder('a')
               ->andWhere('a.scan_status = :status')
               ->setParameter('status', $status)
               ->andWhere('a.api_job_type = :jobType')
               ->setParameter('jobType', 'uploadFiles')
               ->orderBy('a.id', 'ASC')
               ->getQuery()
               ->getResult()
           ;
       }

       /**
        * Return One record of APIjobType
        *
        * @param mixed $value
        * 
        * @return ApiJobs|null
        * 
        */
       public function findOneByDebrickedUploadId($value): ?ApiJobs
       {
           return $this->createQueryBuilder('a')
               ->where('a.debricked_upload_id = :uploadId')
               ->setParameter('uploadId', $value)
               ->andWhere('a.api_job_type = :jobType')
               ->setParameter('jobType', 'uploadFiles')
               ->getQuery()
               ->getOneOrNullResult()
           ;
       }

       /**
        * Return result array filtering by debricked ciUploadId
        *
        * @param mixed $value
        * 
        * @return array|null
        * 
        */
       public function findOneByUploadId($value): ?array
       {
           return $this->createQueryBuilder('a')
               ->where('a.debricked_upload_id = :uploadId')
               ->setParameter('uploadId', $value)
               ->andWhere('a.api_job_type = :jobType')
               ->setParameter('jobType', 'uploadFiles')
               ->getQuery()
               ->getResult()
           ;
       }
}
