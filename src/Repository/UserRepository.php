<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    // Crée une méthode pour charger un utilisateur par Username
    public function loadUserByUsername(string $username): ?User
    {
        $user = $this->findOneBy(['username' => $username]);
        return $user;
    }

    /**
     * Recherche les personnes par nom ou prénom
     *
     * @param string $query Le terme de recherche
     * @return array Les résultats de la recherche
     */
    public function search(string $query): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.firstname LIKE :query OR p.lastname LIKE :query')
            ->andWhere('p.private = :private') // Ajouter la condition pour le champ 'private'
            ->setParameter('query', '%'.$query.'%')
            ->setParameter('private', true) // Spécifier que 'private' doit être vrai
            ->orderBy('p.lastname', 'ASC') // Trier par le nom de famille en ordre croissant
            ->addOrderBy('p.firstname', 'ASC'); // Si vous souhaitez aussi trier par le prénom

        return $qb->getQuery()->getResult();
    }


    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
