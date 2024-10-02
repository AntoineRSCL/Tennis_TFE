<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MyClubController extends AbstractController
{
    #[Route('/myclub', name: 'myclub_index')]
    public function index(): Response
    {
        return $this->render('my_club/index.html.twig', [
            'controller_name' => 'MyClubController',
        ]);
    }
}
