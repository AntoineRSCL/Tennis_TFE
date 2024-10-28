<?php

namespace App\Controller;

use App\Entity\Sponsor;
use App\Form\SponsorType;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AdminSponsorController extends AbstractController
{
    /**
     * Fonction pour voir les sponsors
     *
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/sponsor', name: 'admin_sponsor_index')]
    public function index(EntityManagerInterface $manager): Response
    {
        $sponsors = $manager->getRepository(Sponsor::class)->findAll();

        return $this->render('admin/sponsor/index.html.twig', [
            'sponsors' => $sponsors,
        ]);
    }

    /**
     * Fonction pour créer un sponsor
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/sponsor/new', name: 'admin_sponsor_new')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $sponsor = new Sponsor();
        $form = $this->createForm(SponsorType::class, $sponsor);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload du logo
            $logoFile = $form->get('logo')->getData();
            if ($logoFile) {
                $slugify = new Slugify();
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugify->slugify($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$logoFile->guessExtension();

                // Déplacer le fichier dans le répertoire spécifié
                try {
                    $logoFile->move(
                        $this->getParameter('pictures_directory'), // Assurez-vous que ce paramètre est défini dans votre config
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'exception si quelque chose se passe pendant l'upload
                }

                // Mettre à jour la propriété 'logo' pour stocker le nom du fichier
                $sponsor->setLogo($newFilename);
            }

            // Enregistrer le sponsor
            $manager->persist($sponsor);
            $manager->flush();

            $this->addFlash('success', 'Le sponsor '.$sponsor->getName().' a été créé avec succès.');

            return $this->redirectToRoute('admin_sponsor_index');
        }

        return $this->render('admin/sponsor/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Fonction pour modifier un sponsor
     */
    #[Route('/admin/sponsor/{id}/edit', name: 'admin_sponsor_edit')]
    public function edit(Sponsor $sponsor, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(SponsorType::class, $sponsor);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload du logo
            $logoFile = $form->get('logo')->getData();
            if ($logoFile) {
                // Supprimer l'ancien logo s'il en existe un
                $oldLogo = $sponsor->getLogo();
                if ($oldLogo) {
                    $oldLogoPath = $this->getParameter('pictures_directory').'/'.$oldLogo;
                    if (file_exists($oldLogoPath)) {
                        unlink($oldLogoPath);
                    }
                }

                $slugify = new Slugify();
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugify->slugify($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$logoFile->guessExtension();

                // Déplacer le fichier dans le répertoire spécifié
                try {
                    $logoFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'exception si quelque chose se passe pendant l'upload
                }

                // Mettre à jour la propriété 'logo' pour stocker le nom du fichier
                $sponsor->setLogo($newFilename);
            }

            // Mettre à jour le sponsor
            $manager->flush();

            $this->addFlash('success', 'Le sponsor '.$sponsor->getName().' a été modifié avec succès.');

            return $this->redirectToRoute('admin_sponsor_index');
        }

        return $this->render('admin/sponsor/edit.html.twig', [
            'form' => $form->createView(),
            'sponsor' => $sponsor,
        ]);
    }

    /**
     * Fonction pour supprimer un sponsor
     */
    #[Route('/admin/sponsor/{id}/delete', name: 'admin_sponsor_delete')]
    public function delete(Sponsor $sponsor, EntityManagerInterface $manager): Response
    {
        // Supprimer le logo associé s'il en existe un
        $logo = $sponsor->getLogo();
        if ($logo) {
            $logoPath = $this->getParameter('pictures_directory').'/'.$logo;
            if (file_exists($logoPath)) {
                unlink($logoPath);
            }
        }

        $manager->remove($sponsor);
        $manager->flush();

        $this->addFlash('success', 'Le sponsor '.$sponsor->getName().' a été supprimé avec succès.');

        return $this->redirectToRoute('admin_sponsor_index');
    }
}
