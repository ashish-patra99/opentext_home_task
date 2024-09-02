<?php

namespace App\Repository;

use App\Entity\JobFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobFile>
 */
class JobFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobFile::class);
    }

    //    /**
    //     * @return JobFile[] Returns an array of JobFile objects
    //     */
       public function findByJobId($value): array
       {
           return $this->createQueryBuilder('j')
               ->andWhere('j.api_job_id = :jobId')
               ->setParameter('jobId', $value)
               ->orderBy('j.id', 'ASC')
               ->getQuery()
               ->getResult()
           ;
       }

       /**
        * Returns a JobFile object for a given filename
        *
        * @param mixed $value
        * 
        * @return JobFile|null
        * 
        */
       public function findOneByFileName($value): ?JobFile
       {
           return $this->createQueryBuilder('j')
               ->andWhere('j.file_name = :val')
               ->setParameter('val', $value)
               ->getQuery()
               ->getOneOrNullResult()
           ;
       }
}
