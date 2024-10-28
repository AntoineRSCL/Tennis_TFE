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
    /**
     * Fonction pour afficher l'adresse book
     */
    #[Route('/myclub/addressbook/{page<\d+>?1}', name: 'addressbook_index')]
    public function index(Request $request, PaginationService $paginationService, int $page): Response
    {
        // Configurer le service de pagination pour l'entité User
        $paginationService->setEntityClass(User::class)
            ->setCriteria(['private' => true]);

        // Récupérer les paramètres de tri et de recherche
        $sortOrder = $request->query->get('sortOrder', 'asc');
        $sortField = $request->query->get('sortField', 'lastname');
        $searchTerm = $request->query->get('searchTerm');

        // Définir le critère de recherche
        if ($searchTerm) {
            $paginationService->setSearchTerm($searchTerm);
        }

        // Définir l'ordre de tri
        $paginationService->setOrder([$sortField => $sortOrder]);

        // Récupérer la page actuelle
        $paginationService->setPage($page); // Utiliser la page du paramètre de la route

        // Obtenir les données paginées
        $pagination = $paginationService->getData();

        return $this->render('address_book/index.html.twig', [
            'users' => $pagination,
            'pagination' => $paginationService,
            'sortOrder' => $sortOrder,
            'sortField' => $sortField,
            'searchTerm' => $searchTerm,
        ]);
    }
}
