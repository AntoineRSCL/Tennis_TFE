<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\CoachRepository;
use App\Repository\CourtRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(CoachRepository $coachRepo, UserRepository $userRepo, CourtRepository $courtRepo): Response
    {
        // Récupérer le nombre total de membres, de coachs et de courts
        $totalMembers = $userRepo->count([]);
        $totalCoaches = $coachRepo->count([]);
        $totalCourts = $courtRepo->count([]);

        // Arrondir le nombre de membres à la dizaine inférieure
        $totalMembersRounded = floor($totalMembers / 10) * 10;

        return $this->render('home.html.twig', [
            'totalMembers' => $totalMembersRounded,
            'totalCoaches' => $totalCoaches,
            'totalCourts' => $totalCourts,
        ]);
    }
}
