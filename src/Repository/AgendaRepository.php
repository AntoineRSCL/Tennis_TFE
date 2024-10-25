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

     /**
     * Récupérer les événements à venir triés par date
     *
     * @return Agenda[]
     */
    public function findAllUpcomingEvents(): array
    {
        // Récupérer la date du jour
        $today = new \DateTime('today');

        // Créer la requête pour récupérer les événements à venir
        return $this->createQueryBuilder('a')
            ->where('a.date >= :today')
            ->setParameter('today', $today)
            ->orderBy('a.date', 'ASC')  // Trier par date la plus proche
            ->getQuery()
            ->getResult();
    }

    // Ajouter cette méthode
    public function findUpcomingEventsWithCountForUser($user)
    {
        return $this->createQueryBuilder('e')
            ->select('e.id, e.title, e.date, e.picture, e.slug, COUNT(r.id) AS registrationCount')
            ->leftJoin('e.agendaReservations', 'r')
            ->where('e.date > :now')
            ->setParameter('now', new \DateTime())
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->groupBy('e.id')
            ->having('COUNT(r.id) > 0') // Ajoutez cette ligne
            ->getQuery()
            ->getArrayResult(); // Cela retourne un tableau associatif
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
