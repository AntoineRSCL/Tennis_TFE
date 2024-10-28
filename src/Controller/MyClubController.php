<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use App\Repository\TournamentRepository;
use App\Repository\TournamentMatchRepository;
use App\Repository\AgendaRepository; // Importer le repository
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyClubController extends AbstractController
{
    /**
     * Fonction pour afficher les choses a faire pour l'user connecte
     *
     * @param ReservationRepository $reservationRepository
     * @param TournamentRepository $tournamentRepository
     * @param TournamentMatchRepository $tournamentMatchRepository
     * @param AgendaRepository $agendaRepository
     * @return Response
     */
    #[Route('/myclub', name: 'myclub_index')]
    #[IsGranted('ROLE_USER')]
    public function index(ReservationRepository $reservationRepository, TournamentRepository $tournamentRepository, TournamentMatchRepository $tournamentMatchRepository, AgendaRepository $agendaRepository): Response 
    {
        $user = $this->getUser();

        // Récupération des prochaines réservations de l'utilisateur
        $upcomingReservations = $reservationRepository->findUpcomingReservationsForUser($user);

        // Récupération des prochains matchs de tournoi de l'utilisateur
        $upcomingMatches = $tournamentMatchRepository->findUpcomingMatchesForUser($user);

        // Récupération des événements à venir avec le nombre d'inscriptions
        $upcomingEventsWithCount = $agendaRepository->findUpcomingEventsWithCountForUser($user);

        // Nombre de matchs gagnés
        $matchesWon = $tournamentMatchRepository->countMatchesWonByUser($user);

        // Nombre de tournois gagnés
        $tournamentsWon = $tournamentRepository->countTournamentsWonByUser($user);

        // Nombre total de matchs joués
        $totalMatchesPlayed = $tournamentMatchRepository->countTotalMatchesPlayedByUser($user);

        return $this->render('myclub/index.html.twig', [
            'user' => $user,
            'upcomingReservations' => $upcomingReservations,
            'upcomingMatches' => $upcomingMatches,
            'upcomingEventsWithCount' => $upcomingEventsWithCount,
            'matchesWon' => $matchesWon,
            'tournamentsWon' => $tournamentsWon,
            'totalMatchesPlayed' => $totalMatchesPlayed,
        ]);
    }
}
