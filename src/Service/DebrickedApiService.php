<?php

namespace App\Service;

use App\Entity\ApiJobs;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Cache\CacheInterface;
use App\Service\RedisService;
use Doctrine\ORM\EntityManagerInterface;



/**
 * [Service class to use call all debricked API]
 */
class DebrickedApiService
{
    
    private HttpClientInterface $httpClient;
    private $jwtToken;
    private $apiBaseURL;
    private $accessToken;
    private $ciUploadId;
    private $redis;
    private $entityManager;

    /**
     * [default constructor for DebrickedApiService]
     *
     * @param string $apiBaseURL
     * @param string $accessToken
     * 
     */
    public function __construct(string $apiBaseURL,string $accessToken, RedisService $redis)
    {
        $this->httpClient = HttpClient::create();
        $this->apiBaseURL = $apiBaseURL;
        $this->accessToken = $accessToken;
        $this->redis = $redis;
        $this->jwtToken = '';
    }

    /**
     * Authenticate with accesstoken and get JWT with the Debricked API and store the JWT token.
     */
    public function authenticate(): string
    {
        if(empty($this->jwtToken)){
            $response = $this->httpClient->request('POST', $this->apiBaseURL . 'login_refresh', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'body' => [
                    'refresh_token' => $this->accessToken
                ]
            ]);

            $data = $response->toArray();
            $this->jwtToken = $data['token'] ?? null;
                if (!$this->jwtToken) {
                    throw new \Exception('Failed to get Debricked API.');
                }
            $this->redis->setKeyValue('jwt_Token', $data['token']);
            return $this->jwtToken;
      } 
      return $this->jwtToken;
    
    }

    /**
     * Upload a dependency file to Debricked.
     *
     * @param string $filePath
     * 
     * @return string
     * 
     */
    public function uploadDependencyFiles(string $filePath): string
    {
        $body = [
            'fileData' => fopen($filePath, 'r'),
            'repositoryName' => 'TestRepo',
            'commitName' => 'test commit '
        ];
       
        if(!empty($this->ciUploadId)){
            $body['ciUploadId'] = $this->ciUploadId;
        }
        
        $response = $this->httpClient->request('POST', $this->apiBaseURL . '1.0/open/uploads/dependencies/files', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->authenticate(),
                'Content-Type' => 'multipart/form-data',
            ],
            'body' => $body,
        ]);
        if (400 == $response->getStatusCode()) {
            throw new \Exception('Unsupported File format');
        }
        $data = $response->toArray();
        $this->ciUploadId=$data['ciUploadId'];
        return $this->ciUploadId ?? null;
    }

    /**
     * api call to conclude all file uploads amd queue to scan
     *
     * @param int $uploadId
     * 
     * @return int
     * 
     */
    public function concludeDependencies(int $uploadId): int
    {
        $response = $this->httpClient->request('POST', $this->apiBaseURL . '1.0/open/finishes/dependencies/files/uploads', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->authenticate(),
                'Content-Type' => 'multipart/form-data',
                'Accept' => 'application/json'
            ],
            'body' => [
                'ciUploadId' => $uploadId,
                'returnCommitData' => true
            ]
        ]);
        
        $data = $response->toArray();
        return $data['repositoryId'] ?? null;
    }

    /**
     * Retrieve the results of the dependency analysis.
     */
    public function getFileScanStatus(string $uploadId): array
    {
        $response = $this->httpClient->request('GET', $this->apiBaseURL . '1.0/open/ci/upload/status?ciUploadId='.$uploadId, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->authenticate(),
                'Accept' => '*/*'
            ],
        ]);
        $fulldata = $response->toArray();
        $data = ['ciUploadId'=>$uploadId,'progress'=>$fulldata['progress'],'vulnerabilitiesFound'=>$fulldata['vulnerabilitiesFound']];
       
        return  $data;
    }
}
