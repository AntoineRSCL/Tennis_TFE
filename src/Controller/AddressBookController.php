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

        // Récupérer les paramètres de tri et de recherche
        $sortOrder = $request->query->get('sortOrder', 'asc'); // 'asc' ou 'desc'
        $sortField = $request->query->get('sortField', 'lastname'); // champ par défaut à trier
        $searchTerm = $request->query->get('searchTerm'); // terme de recherche

        // Définir le critère de recherche
        if ($searchTerm) {
            $paginationService->setSearchTerm($searchTerm);
        }

        // Définir l'ordre de tri
        $paginationService->setOrder([$sortField => $sortOrder]);

        // Récupérer la page actuelle
        $currentPage = $request->query->getInt('page', 1);
        $paginationService->setPage($currentPage);

        // Obtenir les données paginées
        $pagination = $paginationService->getData();

        return $this->render('address_book/index.html.twig', [
            'users' => $pagination,      // Renvoie les utilisateurs
            'pagination' => $paginationService, // Renvoie les informations de pagination
            'sortOrder' => $sortOrder,   // Ajout de la variable sortOrder
            'sortField' => $sortField,    // Ajout de la variable sortField
            'searchTerm' => $searchTerm,   // Ajout de la variable searchTerm
        ]);
    }
}
