<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_BE');

        $user = new User();
        $user->setUsername('BautAntoine')
        ->setPassword($this->passwordHasher->hashPassword($user, 'password'))
        ->setRoles(["ROLE_ADMIN"])
        ->setFirstname('Antoine')
        ->setLastName('Baut')
        ->setEmail("abaut2001@gmail.com")
        ->setPhone("+32/470.326.783")
        ->setRanking("B+2/6")
        ->setDoubleRanking("70")
        ->setBirthDate(new \DateTime('2001-06-28'))
        ->setPrivate(false)
        ->setAddressVerified(false);

        
        $manager->persist($user);

        $rankings = ['C30.6', 'C30.5', 'C30.4', 'C30.3', 'C30.2', 'C30.1', 'C30', 'C15.5', 'C15.4', 'C15.3', 'C15.2', 'C15.1', 'C15', 'B+4/6', 'B+2/6', 'B0', 'B-2/6', 'B-4/6', 'B-15', 'B-15.1', 'B-15.2', 'B-15.4', 'A.Nat', 'A.Int'];
        $doubleRankings = [3, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100, 105, 110, 115];

        // Fonction pour obtenir le classement double à partir du classement
        function getDoubleRanking($ranking, $rankings, $doubleRankings) {
            $index = array_search($ranking, $rankings);
            return $index !== false ? $doubleRankings[$index] : null;
        }

        // Création et persistance des membres
        for ($i = 1; $i <= 25; $i++) {
            $user = new User();
            $ranking = $rankings[array_rand($rankings)];
            $doubleRanking = getDoubleRanking($ranking, $rankings, $doubleRankings);

            $user->setUsername($faker->unique()->userName())
                ->setPassword($this->passwordHasher->hashPassword($user, 'password'))
                ->setRoles(["ROLE_USER"])
                ->setFirstname($faker->firstName())
                ->setLastName($faker->lastName())
                ->setEmail($faker->unique()->email())
                ->setPhone($faker->phoneNumber())
                ->setRanking($ranking)
                ->setDoubleRanking($doubleRanking)
                ->setBirthDate($faker->dateTimeBetween('-50 years', '-20 years'))
                ->setPrivate($faker->boolean())
                ->setAddressVerified($faker->boolean());

            $manager->persist($user);
        }

        $manager->flush();
    }
}
