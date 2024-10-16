<?php

namespace App\Repository;

use App\Entity\Agenda;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Agenda>
 */
class AgendaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agenda::class);
    }

    /**
     * Récupère les 4 événements les plus proches de la date actuelle (sans inclure les événements passés).
     *
     * @return Agenda[]
     */
    public function findUpcomingEvents(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.date >= :currentDate')
            ->setParameter('currentDate', new \DateTime()) // On récupère la date actuelle
            ->orderBy('a.date', 'ASC') // Trier par date croissante
            ->setMaxResults(4) // Limiter les résultats à 4 événements
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Agenda[] Returns an array of Agenda objects
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

//    public function findOneBySomeField($value): ?Agenda
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
