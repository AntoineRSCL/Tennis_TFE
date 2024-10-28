<?php

namespace App\Controller;

use App\Repository\NewsRepository;
use App\Repository\UserRepository;
use App\Repository\CoachRepository;
use App\Repository\CourtRepository;
use App\Repository\AgendaRepository;
use App\Repository\SponsorRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * Fonction pour la page Home
     *
     * @param CoachRepository $coachRepo
     * @param UserRepository $userRepo
     * @param CourtRepository $courtRepo
     * @param NewsRepository $newsRepo
     * @param AgendaRepository $agendaRepo
     * @param SponsorRepository $sponsorsRepo
     * @return Response
     */
    #[Route('/', name: 'homepage')]
    public function index(CoachRepository $coachRepo, UserRepository $userRepo, CourtRepository $courtRepo, NewsRepository $newsRepo, AgendaRepository $agendaRepo, SponsorRepository $sponsorsRepo): Response
    {
        // Récupérer le nombre total de membres, de coachs et de courts
        $totalMembers = $userRepo->count([]);
        $totalCoaches = $coachRepo->count([]);
        $totalCourts = $courtRepo->count([]);

        // Arrondir le nombre de membres à la dizaine inférieure
        $totalMembersRounded = floor($totalMembers / 10) * 10;

        $lastNews = $newsRepo->findLastFourNews();
        $upcomingEvents = $agendaRepo->findUpcomingEvents();
        $listSponsors = $sponsorsRepo->findAll();


        return $this->render('home.html.twig', [
            'totalMembers' => $totalMembersRounded,
            'totalCoaches' => $totalCoaches,
            'totalCourts' => $totalCourts,
            'lastNews' => $lastNews,
            'upcomingEvents' => $upcomingEvents,
            'listSponsors' => $listSponsors,
        ]);
    }

    /**
     * Fonction pour la page a propos
     *
     * @param CoachRepository $coachRepo
     * @return Response
     */
    #[Route('/about', name: 'about')]
    public function about(CoachRepository $coachRepo): Response
    {
        // Récupérer le nombre total de membres, de coachs et de courts
        $totalCoaches = $coachRepo->findAll();


        return $this->render('about/index.html.twig', [
            'totalCoaches' => $totalCoaches,
        ]);
    }

    /**
     * Fonction pour la page legales
     *
     * @return Response
     */
    #[Route('/legals', name: 'legals')]
    public function legals(): Response
    {
        return $this->render('legals/legals.html.twig', [
        ]);
    }

    /**
     * Fonction pour la page R O I
     *
     * @return Response
     */
    #[Route('/reglement', name: 'reglement')]
    public function reglement(): Response
    {
        return $this->render('legals/reglement.html.twig', [
        ]);
    }
}
