<?php

namespace App\Repository;

use App\Entity\AgendaReservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AgendaReservation>
 */
class AgendaReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendaReservation::class);
    }

    public function findByAgenda($agendaId)
    {
        return $this->createQueryBuilder('ar')
            ->andWhere('ar.agenda = :agendaId')
            ->setParameter('agendaId', $agendaId)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return AgendaReservation[] Returns an array of AgendaReservation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?AgendaReservation
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
