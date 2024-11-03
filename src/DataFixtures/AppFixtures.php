<?php

namespace App\DataFixtures;

use App\Entity\Reservation;
use App\Entity\User;
use App\Entity\Court; // Assurez-vous d'avoir l'entité Court
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Récupération de tous les utilisateurs et courts existants
        $users = $manager->getRepository(User::class)->findAll();
        $courts = $manager->getRepository(Court::class)->findAll(); // Assurez-vous que l'entité Court existe

        // Vérifiez qu'il y a des utilisateurs
        if (count($users) === 0) {
            throw new \Exception('Aucun utilisateur trouvé. Veuillez d\'abord créer des utilisateurs.');
        }

        // Vérifiez qu'il y a des courts
        if (count($courts) === 0) {
            throw new \Exception('Aucun court trouvé. Veuillez d\'abord créer des courts.');
        }

        $reservationsCount = 0;
        $maxReservationsPerUser = 4;
        $totalReservations = 1000; // Nombre total de réservations à créer
        $startDate = new \DateTime('2024-11-04'); // Date de départ pour les réservations
        $endDate = new \DateTime('2024-11-11'); // Date de fin pour les réservations

        // Définir les horaires d'ouverture (par exemple, de 9h00 à 21h00)
        $openingHour = 8;
        $closingHour = 23;

        // Tableau pour suivre les réservations déjà créées
        $existingReservations = [];

        // Boucle à travers chaque utilisateur
        foreach ($users as $user) {
            // Boucle à travers chaque date entre le 4 et le 11 novembre
            for ($currentDate = clone $startDate; $currentDate <= $endDate; $currentDate->modify('+1 day')) {
                for ($i = 0; $i < $maxReservationsPerUser && $reservationsCount < $totalReservations; $i++) {
                    // Choisir un court aléatoire
                    $court = $courts[array_rand($courts)];

                    // Créer une heure de début aléatoire dans les horaires d'ouverture
                    $hour = rand($openingHour, $closingHour - 1); // -1 pour éviter de dépasser l'heure de fermeture
                    $startTime = (clone $currentDate)->setTime($hour, 0); // Heure de début aléatoire
                    $endTime = (clone $startTime)->modify('+1 hour'); // Durée d'une heure

                    // Vérifier si cette combinaison court + heure existe déjà
                    $reservationKey = $court->getId() . '|' . $startTime->format('Y-m-d H:i:s');
                    if (!in_array($reservationKey, $existingReservations)) {
                        // Choisir un autre utilisateur pour player2
                        $player2 = $user;
                        while ($player2 === $user) {
                            $player2 = $users[array_rand($users)];
                        }

                        $reservation = new Reservation();
                        $reservation->setStartTime($startTime);
                        $reservation->setEndTime($endTime);
                        $reservation->setCourt($court);
                        $reservation->setPlayer1($user);
                        $reservation->setPlayer2($player2); // Assurez-vous que player2 n'est pas le même que player1

                        $manager->persist($reservation);
                        $existingReservations[] = $reservationKey; // Ajouter à la liste des réservations existantes
                        $reservationsCount++;
                    }
                }

                // Sortir de la boucle si le nombre de réservations a été atteint
                if ($reservationsCount >= $totalReservations) {
                    break 2; // Sortir de toutes les boucles
                }
            }
        }

        $manager->flush();
    }
}
