<?php

namespace App\Controller;

use App\Entity\Court;
use App\Form\CourtType;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCourtController extends AbstractController
{
    #[Route('/admin/court/{page<\d+>?1}', name: 'admin_court_index')]
    public function index(PaginationService $pagination, int $page): Response
    {
        $pagination->setEntityClass(Court::class)
                   ->setPage($page)
                   ->setLimit(9);

        return $this->render('admin/court/index.html.twig', [
            'pagination' => $pagination,            
        ]);
    }


    #[Route('/admin/court/new', name: 'admin_court_new')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $court = new Court();
        $form = $this->createForm(CourtType::class, $court);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $manager->persist($court);
            $manager->flush();

            $this->addFlash('success', 'Le court n° '.$court->getId().' a été créé avec succès.');

            return $this->redirectToRoute('admin_court_index');
        }

        return $this->render('admin/court/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/court/{id}/edit', name: 'admin_court_edit')]
    public function edit(Court $court, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(CourtType::class, $court);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash('success', 'Le terrain n'.$court->getId().' a été modifié avec succès.');
            return $this->redirectToRoute('admin_court_index');
        }

        return $this->render('admin/court/edit.html.twig', [
            'form' => $form->createView(),
            'court' => $court,
        ]);
    }

    #[Route('/admin/court/{id}/delete', name: 'admin_court_delete')]
    public function delete(Court $court, EntityManagerInterface $manager, Request $request): Response
    {
        $manager->remove($court);
        $manager->flush();
        $this->addFlash(
            'success',
            'L evenement n°<strong>'.$court->getId().'</strong> a bien été supprimé'
        );

        return $this->redirectToRoute('admin_court_index');
    }

}
