<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\PaginationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AddressBookController extends AbstractController
{
    #[Route('/myclub/addressbook', name: 'addressbook_index')]
    public function index(Request $request, PaginationService $paginationService): Response
    {
        // Configurer le service de pagination pour l'entité User
        $paginationService->setEntityClass(User::class);

        // Définir le critère pour ne récupérer que les utilisateurs privés
        $paginationService->setCriteria(['private' => true]);

        // Récupérer la page actuelle
        $currentPage = $request->query->getInt('page', 1);
        $paginationService->setPage($currentPage);

        // Obtenir les données paginées
        $pagination = $paginationService->getData();

        return $this->render('address_book/index.html.twig', [
            'users' => $pagination,      // Renvoie les utilisateurs
            'pagination' => $paginationService, // Renvoie les informations de pagination
        ]);
    }
}
