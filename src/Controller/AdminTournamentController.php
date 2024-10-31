<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Tournament;
use App\Form\TournamentType;
use App\Entity\TournamentMatch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminTournamentController extends AbstractController
{
    /**
     * Fonction pour afficher un tournoi
     *
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/admin/tournament', name: 'admin_tournament_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les tournois depuis la base de données
        $tournaments = $entityManager->getRepository(Tournament::class)->findAll();

        return $this->render('admin/tournament/index.html.twig', [
            'tournaments' => $tournaments, // Passer la variable à Twig
        ]);
    }

    /**
     * Fonction pour creer un tournoi
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/admin/tournament/new', name: 'admin_tournament_new')]
    public function createTournament(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tournament = new Tournament();
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tournament->setStatus('Inscription');

            $pictureFile = $form->get('image')->getData();
            if ($pictureFile) {
                $newFilename = uniqid().'.'.$pictureFile->guessExtension();
                $pictureFile->move(
                    $this->getParameter('pictures_directory'), // Directory to store files
                    $newFilename
                );
                $tournament->setImage($newFilename);
            }

            $entityManager->persist($tournament);
            $entityManager->flush();

            // Générer automatiquement les matchs après la création du tournoi
            $this->generateMatches($tournament, $entityManager);

            $this->addFlash('success', 'Tournoi et matchs créés avec succès !');

            return $this->redirectToRoute('admin_tournament_index');
        }

        return $this->render('admin/tournament/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Fonction pour gérer les matchs
     *
     * @param Tournament $tournament
     * @param EntityManagerInterface $entityManager
     * @return void
     */
    private function generateMatches(Tournament $tournament, EntityManagerInterface $entityManager)
    {
        $maxParticipants = $tournament->getParticipantsMax();
        $participants = $tournament->getTournamentRegistrations()->toArray();

        // Vérifier qu'il y a assez de participants
        if (count($participants) < $maxParticipants) {
            $this->addFlash('error', 'Pas assez de participants pour générer des matchs.');
            return;
        }

        // Créer les matchs pour le premier tour uniquement
        $numMatches = $maxParticipants / 2; // Nombre de matchs pour le premier tour

        // Mélanger les participants pour le tirage au sort
        shuffle($participants);

        for ($i = 0; $i < $numMatches; $i++) {
            $match = new TournamentMatch();
            $match->setTournament($tournament);
            $match->setRound('Tour 1');

            // Assigner les joueurs au match à partir des inscriptions
            $match->setPlayer1($participants[$i * 2]->getPlayer()); // Utiliser getPlayer() ici
            $match->setPlayer2($participants[$i * 2 + 1]->getPlayer()); // Utiliser getPlayer() ici

            $entityManager->persist($match);
        }

        
        $tournament->setStatus('En cours'); // Définir le statut à "en cours"
        $entityManager->flush();
    }


    /**
     * Fonction pour lancer un tournoi
     */
    #[Route('/admin/tournament/start/{id}', name: 'admin_tournament_start')]
    public function startTournament(Tournament $tournament, EntityManagerInterface $entityManager): Response
    {
        // Assurer que le tournoi a des participants
        if ($tournament->getTournamentRegistrations()->isEmpty()) {
            $this->addFlash('danger', 'Le tournoi doit avoir des participants pour commencer.');
            return $this->redirectToRoute('admin_tournament_index');
        }

        // Vérifier si le tournoi a déjà été démarré
        if ($tournament->getCurrentRound() > 0) {
            $this->addFlash('danger', 'Le tournoi a déjà commencé.');
            return $this->redirectToRoute('admin_tournament_index');
        }

        // Vérifier si le nombre d'inscrits a atteint le maximum
        $maxParticipants = $tournament->getParticipantsMax(); // Assurez-vous que cette méthode existe
        $currentParticipantsCount = $tournament->getTournamentRegistrations()->count();

        if ($currentParticipantsCount < $maxParticipants) {
            $this->addFlash('danger', 'Le nombre maximum de participants n\'a pas été atteint.');
            return $this->redirectToRoute('admin_tournament_index');
        }

        // Générer les matchs pour le premier tour
        $this->generateMatches($tournament, $entityManager);

        $tournament->setCurrentRound(1); // Initialiser le round actuel
        $entityManager->persist($tournament);
        $entityManager->flush();

        $this->addFlash('success', 'Le tournoi a démarré avec succès.');
        return $this->redirectToRoute('admin_tournament_index');
    }


    /**
     * Fonction pour generer le prochain tour d'un tournoi
     */
    #[Route('/admin/tournament/generate-next-round/{id}', name: 'admin_tournament_generate_next_round')]
    public function generateNextRound(Tournament $tournament, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si le tournoi a déjà des rounds
        if ($tournament->getCurrentRound() === 0) {
            $this->addFlash('danger', 'Le tournoi n\'a pas encore commencé.');
            return $this->redirectToRoute('admin_tournament_index');
        }

        // Récupérer les gagnants des matchs du dernier tour
        $lastRoundMatches = $entityManager->getRepository(TournamentMatch::class)->findBy([
            'tournament' => $tournament,
            'round' => 'Tour ' . $tournament->getCurrentRound()
        ]);

        $winners = [];
        foreach ($lastRoundMatches as $match) {
            if ($match->getWinner()) {
                $winners[] = $match->getWinner();
            }
        }

        // Vérifier qu'il y a assez de gagnants pour le prochain tour
        if (count($winners) < 2) {
            $this->addFlash('danger', 'Pas assez de gagnants pour générer le prochain tour.');
            return $this->redirectToRoute('admin_tournament_index');
        }

        // Créer les matchs pour le prochain tour
        $numMatches = count($winners) / 2; 
        for ($i = 0; $i < $numMatches; $i++) {
            $match = new TournamentMatch();
            $match->setTournament($tournament);
            $nextRound = 'Tour ' . ($tournament->getCurrentRound() + 1); 
            $match->setRound($nextRound);
            
            // Assigner les gagnants au match
            $match->setPlayer1($winners[$i * 2]);
            $match->setPlayer2($winners[$i * 2 + 1]);

            $entityManager->persist($match);
        }

        // Mettre à jour le round courant
        $tournament->setCurrentRound($tournament->getCurrentRound() + 1);
        
        // Pas de changement de statut ici
        $this->addFlash('success', 'Le prochain tour a été généré avec succès.');
        $entityManager->flush();
        return $this->redirectToRoute('admin_tournament_index');
    }


    /**
     * Fonction pour connaitre le nombre de tour
     *
     * @param integer $participantsMax
     * @return integer
     */
    private function calculateTotalRounds(int $participantsMax): int
    {
        if ($participantsMax == 4) {
            return 2; // 2 tours pour 4 participants
        } elseif ($participantsMax == 8) {
            return 3; // 3 tours pour 8 participants
        } elseif ($participantsMax == 16) {
            return 4; // 4 tours pour 16 participants
        } elseif ($participantsMax == 32) {
            return 5; // 5 tours pour 32 participants
        }
        return 0; // Pas de tours pour un nombre non pris en charge
    }

    /**
     * Fonction pour voir la liste des matchs du tournoi
     */
    #[Route('/admin/tournament/{id}/matches', name: 'admin_tournament_matches')]
    public function tournamentMatches(Tournament $tournament, EntityManagerInterface $entityManager, Request $request): Response
    {
        $matches = $entityManager->getRepository(TournamentMatch::class)->findBy(['tournament' => $tournament]);

        return $this->render('admin/tournament/matches.html.twig', [
            'tournament' => $tournament,
            'matches' => $matches,
        ]);
    }

    /**
     * Fonction pour choisir le vainqueur des matchs du tournoi
     */
    #[Route('/admin/match/{id}/winner', name: 'admin_match_winner')]
    public function setWinner(TournamentMatch $match, Request $request, EntityManagerInterface $entityManager): Response
    {
        $winnerId = $request->request->get('winner'); // Supposons que le vainqueur est passé en paramètre

        if ($winnerId) {
            $winner = $entityManager->getRepository(User::class)->find($winnerId);
            if ($winner) {
                $match->setWinner($winner);
                $entityManager->persist($match);
                $entityManager->flush();

                // Vérifier si le match est le dernier pour ce tournoi
                $tournament = $match->getTournament();
                $currentRound = $tournament->getCurrentRound();
                $totalRounds = $this->calculateTotalRounds($tournament->getParticipantsMax());

                if ($currentRound === $totalRounds) {
                    $tournament->setStatus('Terminé'); // Mettez à jour le statut à "terminé"
                    $tournament->setWinner($winner);
                    $entityManager->persist($tournament);
                    $entityManager->flush();
                }

                $this->addFlash('success', 'Vainqueur enregistré avec succès !');
            } else {
                $this->addFlash('error', 'Joueur non trouvé.');
            }
        }

        return $this->redirectToRoute('admin_tournament_matches', ['id' => $tournament->getId()]);
    }

    /**
     * Fonction pour supprimer un tournoi
     */
    #[Route('/admin/tournament/delete/{id}', name: 'admin_tournament_delete')]
    public function deleteTournament(Tournament $tournament, EntityManagerInterface $entityManager): Response
    {
        // Supprimer les matchs associés au tournoi
        foreach ($tournament->getTournamentMatches() as $match) {
            $entityManager->remove($match);
        }

        // Supprimer les inscriptions associées au tournoi
        foreach ($tournament->getTournamentRegistrations() as $registration) {
            $entityManager->remove($registration);
        }

        // Supprimer le tournoi
        $entityManager->remove($tournament);
        $entityManager->flush();

        $this->addFlash('success', 'Tournoi supprimé avec succès.');

        return $this->redirectToRoute('admin_tournament_index');
    }

}
