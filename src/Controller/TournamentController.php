<?php

namespace App\Controller;

use App\Entity\Tournament;
use App\Repository\UserRepository;
use App\Entity\TournamentRegistration;
use App\Repository\TournamentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TournamentRegistrationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TournamentController extends AbstractController
{
    #[Route('/myclub/tournaments', name: 'tournament_index')]
    public function index(TournamentRepository $tournamentRepository, TournamentRegistrationRepository $tournamentRegistrationRepository): Response
    {
        $user = $this->getUser();
        $tournaments = $tournamentRepository->findAll();
        $tournamentRegistrations = [];

        // Récupérer toutes les inscriptions de l'utilisateur pour les tournois
        foreach ($tournaments as $tournament) {
            $registration = $tournamentRegistrationRepository->findOneBy([
                'tournament' => $tournament,
                'player' => $user,
            ]);
            $tournamentRegistrations[$tournament->getId()] = $registration !== null;
        }

         // Trier les tournois par statut
        usort($tournaments, function ($a, $b) {
            return strcmp($a->getStatus(), $b->getStatus());
        });

        return $this->render('tournament/index.html.twig', [
            'tournaments' => $tournaments,
            'tournamentRegistrations' => $tournamentRegistrations, // Bien transmettre cette variable à Twig
        ]);
    }

    #[Route('/myclub/tournament/{id}/register', name: 'tournament_register')]
    public function register(int $id, TournamentRepository $tournamentRepository, TournamentRegistrationRepository $tournamentRegistrationRepository, EntityManagerInterface $entityManager): Response {
        $user = $this->getUser(); // Récupérer l'utilisateur connecté
        $tournament = $tournamentRepository->find($id);
    
        if (!$tournament) {
            $this->addFlash('danger', 'Le tournoi n\'existe pas.');
            return $this->redirectToRoute('tournament_index');
        }
    
        // Vérifier si l'utilisateur est déjà inscrit
        $existingRegistration = $tournamentRegistrationRepository->findOneBy([
            'tournament' => $tournament,
            'player' => $user
        ]);
    
        if ($existingRegistration) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à ce tournoi.');
            return $this->redirectToRoute('tournament_index');
        }
    
        // Vérifier le classement de l'utilisateur avant inscription
        $userRanking = $user->getDoubleRanking();
        $tournamentRankingMin = $tournament->getRankingMin();
        $tournamentRankingMax = $tournament->getRankingMax();
    
        if (($tournamentRankingMin && $userRanking < $tournamentRankingMin) || ($tournamentRankingMax && $userRanking > $tournamentRankingMax)) {
            $this->addFlash('danger', 'Votre classement ne correspond pas aux critères du tournoi.');
            return $this->redirectToRoute('tournament_index');
        }
    
        // Vérifier si le nombre maximum de participants est atteint
        $participantsCount = count($tournament->getTournamentRegistrations());
        if ($participantsCount >= $tournament->getParticipantsMax()) {
            $this->addFlash('danger', 'Le nombre maximum de participants pour ce tournoi a été atteint.');
            return $this->redirectToRoute('tournament_index');
        }
    
        // Créer une nouvelle inscription
        $registration = new TournamentRegistration();
        $registration->setTournament($tournament);
        $registration->setPlayer($user);
        $registration->setCreatedAt(new \DateTime());
    
        // Sauvegarder l'inscription
        $entityManager->persist($registration);
        $entityManager->flush();
    
        $this->addFlash('success', 'Vous êtes inscrit au tournoi.');
    
        return $this->redirectToRoute('tournament_index');
    }
    

    #[Route('/myclub/tournament/{id}/unregister', name: 'tournament_unregister')]
    public function unregister(Tournament $tournament, TournamentRegistrationRepository $registrationRepo, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $registration = $registrationRepo->findOneBy(['tournament' => $tournament, 'player' => $user]);

        if (!$registration) {
            $this->addFlash('error', 'Vous n\'êtes pas inscrit à ce tournoi.');
            return $this->redirectToRoute('tournament_index');
        }

        $em->remove($registration);
        $em->flush();

        $this->addFlash('success', 'Vous vous êtes désinscrit du tournoi.');
        return $this->redirectToRoute('tournament_index');
    }

    #[Route('/myclub/tournament/{id}/bracket', name: 'tournament_view_bracket')]
    public function viewBracket(Tournament $tournament): Response
    {
        $matches = $tournament->getTournamentMatches();

        return $this->render('tournament/bracket.html.twig', [
            'tournament' => $tournament,
            'matches' => $matches,
        ]);
    }
}
