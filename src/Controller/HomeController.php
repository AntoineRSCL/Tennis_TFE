<?php

namespace App\Controller;

use App\Repository\NewsRepository;
use App\Repository\UserRepository;
use App\Repository\CoachRepository;
use App\Repository\CourtRepository;
use App\Repository\AgendaRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(CoachRepository $coachRepo, UserRepository $userRepo, CourtRepository $courtRepo, NewsRepository $newsRepo, AgendaRepository $agendaRepo): Response
    {
        // Récupérer le nombre total de membres, de coachs et de courts
        $totalMembers = $userRepo->count([]);
        $totalCoaches = $coachRepo->count([]);
        $totalCourts = $courtRepo->count([]);

        // Arrondir le nombre de membres à la dizaine inférieure
        $totalMembersRounded = floor($totalMembers / 10) * 10;

        $lastNews = $newsRepo->findLastFourNews();
        $upcomingEvents = $agendaRepo->findUpcomingEvents();


        return $this->render('home.html.twig', [
            'totalMembers' => $totalMembersRounded,
            'totalCoaches' => $totalCoaches,
            'totalCourts' => $totalCourts,
            'lastNews' => $lastNews,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    #[Route('/apropos', name: 'about')]
    public function about(CoachRepository $coachRepo): Response
    {
        // Récupérer le nombre total de membres, de coachs et de courts
        $totalCoaches = $coachRepo->findAll();


        return $this->render('home.html.twig', [
            'totalCoaches' => $totalCoaches,
        ]);
    }
}
