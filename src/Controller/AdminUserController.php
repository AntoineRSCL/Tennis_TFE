<?php

namespace App\Controller;

use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User; // Assurez-vous d'importer l'entité User
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUserController extends AbstractController
{
    #[Route('/admin/user/{page<\d+>?1}', name: 'admin_user_index')]
    public function index(PaginationService $pagination, int $page): Response
    {
        $pagination->setEntityClass(User::class)
                   ->setPage($page)
                   ->setLimit(9);

        return $this->render('admin/user/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/admin/user/delete/{id}', name: 'admin_user_delete')]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        try {
            $user = $entityManager->getRepository(User::class)->find($id);

            if (!$user) {
                $this->addFlash('error', 'L\'utilisateur n\'existe pas.');
                return $this->redirectToRoute('app_admin_user');
            }

            // Suppression de l'utilisateur
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur a été supprimé avec succès.');

        } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $e) {
            // Gestion de l'exception de contrainte de clé étrangère
            $this->addFlash('danger', 'Impossible de supprimer l\'utilisateur car il est référencé dans d\'autres enregistrements.');
        } catch (\Exception $e) {
            // Gestion d'autres exceptions
            $this->addFlash('danger', 'Une erreur est survenue lors de la suppression de l\'utilisateur.');
        }

        return $this->redirectToRoute('admin_user_index');
    }
}
