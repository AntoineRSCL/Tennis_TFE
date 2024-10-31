<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\StatsService;

class AdminDashboardController extends AbstractController
{
    /**
     * Fonction pour le dashboard
     *
     * @param StatsService $statsService
     * @return Response
     */
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(StatsService $statsService): Response
    {
        // Récupération des statistiques via StatsService
        $userCount = $statsService->getUsersCount();
        $coachCount = $statsService->getCoachCount(); // Renommé à coachCount
        $tournamentCount = $statsService->getTournamentCount(); // Renommé à tournamentCount
        $newsCount = $statsService->getNewsCount(); // Renommé à newsCount
        $agendaCount = $statsService->getAgendaCount(); // Renommé à agendaCount
        $contactCount = $statsService->getContactCount(); // Renommé à contactCount
        $courtCount = $statsService->getCourtCount(); // Renommé à courtCount
        $languageCount = $statsService->getLanguageCount(); // Renommé à languageCount
        $sponsorCount = $statsService->getSponsorCount(); // Renommé à sponsorCount

        // Passer les statistiques au template
        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => [
                'userCount' => $userCount,
                'coachCount' => $coachCount,
                'tournamentCount' => $tournamentCount,
                'newsCount' => $newsCount,
                'agendaCount' => $agendaCount,
                'contactCount' => $contactCount,
                'courtCount' => $courtCount,
                'languageCount' => $languageCount,
                'sponsorCount' => $sponsorCount,
            ]
        ]);
    }
}
