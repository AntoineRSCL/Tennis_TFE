<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\PaginationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AddressBookController extends AbstractController
{
    /**
     * Fonction pour afficher tous les utilisateurs dans l'annuaire
     */
    #[Route('/myclub/addressbook/{page<\d+>?1}', name: 'addressbook_index')]
    public function index(PaginationService $paginationService, int $page): Response
    {
        // Configurer le service de pagination pour l'entité User
        $paginationService->setEntityClass(User::class)
            ->setCriteria(['private' => true])
            ->setOrder(['lastname' => 'ASC', 'firstname' => 'ASC']); // Trier par nom et prénom

        // Récupérer la page actuelle
        $paginationService->setPage($page); // Utiliser la page du paramètre de la route

        // Obtenir les données paginées
        $pagination = $paginationService->getData();

        return $this->render('address_book/index.html.twig', [
            'users' => $pagination,
            'pagination' => $paginationService,
        ]);
    }

    /**
     * Fonction pour rechercher dans l'annuaire
     */
    #[Route('/myclub/addressbook/search', name: 'addressbook_search')]
    public function search(Request $request, UserRepository $userRepo): Response
    {
        $query = $request->query->get('q');

        if (!$query) {
            return $this->redirectToRoute('addressbook_index');
        }

        // Utilisez la méthode search du repository pour obtenir les résultats de recherche
        $results = $query ? $userRepo->search($query) : [];

        return $this->render('address_book/search.html.twig', [
            'results' => $results,
        ]);
    }
}
