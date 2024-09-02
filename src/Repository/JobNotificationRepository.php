<?php

namespace App\Repository;

use App\Entity\JobNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobNotification>
 */
class JobNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobNotification::class);
    }

   /**
    * @return JobNotification[] Returns an array of JobNotification objects
    */
   public function findByApiJobId($value): array
   {
       return $this->createQueryBuilder('j')
           ->andWhere('j.api_job_id_id = :val')
           ->setParameter('val', $value)
           ->orderBy('j.id', 'ASC')
           ->setMaxResults(10)
           ->getQuery()
           ->getResult()
       ;
   }

//    public function findOneBySomeField($value): ?JobNotification
//    {
//        return $this->createQueryBuilder('j')
//            ->andWhere('j.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
