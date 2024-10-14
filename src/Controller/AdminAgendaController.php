<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Form\AgendaType;
use Cocur\Slugify\Slugify;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAgendaController extends AbstractController
{
    /**
     * Fonction pour afficher tous les evenements
     */
    #[Route('/admin/agenda/{page<\d+>?1}', name: 'admin_agenda_index')]
    public function index(PaginationService $pagination, int $page): Response
    {
        // Configurer le service de pagination
        $pagination->setEntityClass(Agenda::class)
                   ->setPage($page)
                   ->setLimit(9);

        return $this->render('admin/agenda/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * Fonction pour rajouter un eveneement
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/agenda/new', name: 'admin_agenda_new')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $agenda = new Agenda();
        $form = $this->createForm(AgendaType::class, $agenda);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $pictureFile = $form->get('picture')->getData();
            if ($pictureFile) {
                $slugify = new Slugify();
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugify->slugify($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();

                // Move the file to the directory where pictures are stored
                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle exception if something happens during file upload
                }

                // Update the 'picture' property to store the file name instead of its contents
                $agenda->setPicture($newFilename);
            }

            $manager->persist($agenda);
            $manager->flush();

            $this->addFlash('success', 'L\'évenement n° '.$agenda->getId().' a été créé avec succès.');

            return $this->redirectToRoute('admin_agenda_index');
        }

        return $this->render('admin/agenda/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Fonction pour editer un evenement
     */
    #[Route('/admin/agenda/{id}/edit', name: 'admin_agenda_edit')]
    public function edit(Agenda $agenda, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(AgendaType::class, $agenda);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $pictureFile = $form->get('picture')->getData();
            if ($pictureFile) {
                // Supprimer l'ancienne image s'il en existe une
                $oldPicture = $agenda->getPicture();
                if ($oldPicture) {
                    $oldPicturePath = $this->getParameter('pictures_directory').'/'.$oldPicture;
                    if (file_exists($oldPicturePath)) {
                        unlink($oldPicturePath);
                    }
                }
                
                $slugify = new Slugify();
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugify->slugify($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();

                // Move the file to the directory where pictures are stored
                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle exception if something happens during file upload
                }

                // Update the 'picture' property to store the file name instead of its contents
                $agenda->setPicture($newFilename);
            }

            $manager->flush();

            $this->addFlash('success', 'L evenement n'.$agenda->getId().' a été modifié avec succès.');
            return $this->redirectToRoute('admin_agenda_index');
        }

        return $this->render('admin/agenda/edit.html.twig', [
            'form' => $form->createView(),
            'agenda' => $agenda,
        ]);
    }

    /**
     * Fonction pour supprimer un evenement
     */
    #[Route('/admin/agenda/{id}/delete', name: 'admin_agenda_delete')]
    public function delete(Agenda $agenda, EntityManagerInterface $manager, Request $request): Response
    {
        // Supprimer l'image associée s'il en existe une
        $picture = $agenda->getPicture();
        if ($picture) {
            $picturePath = $this->getParameter('pictures_directory').'/'.$picture;
            if (file_exists($picturePath)) {
                unlink($picturePath);
            }
        }

        $this->addFlash(
            'success',
            'L evenement n°<strong>'.$agenda->getId().'</strong> a bien été supprimé'
        );
        $manager->remove($agenda);
        $manager->flush();

        return $this->redirectToRoute('admin_agenda_index');
    }
}
