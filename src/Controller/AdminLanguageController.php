<?php

namespace App\Controller;

use App\Entity\Language;
use App\Form\LanguageType;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AdminLanguageController extends AbstractController
{
    #[Route('/admin/language', name: 'admin_language_index')]
    public function index(EntityManagerInterface $manager): Response
    {
        $languages = $manager->getRepository(Language::class)->findAll();

        return $this->render('admin/language/index.html.twig', [
            'languages' => $languages,
        ]);
    }

    #[Route('/admin/language/new', name: 'admin_language_new')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $language = new Language();
        $form = $this->createForm(LanguageType::class, $language);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload du fichier
            $flagFile = $form->get('flagImage')->getData();
            if ($flagFile) {
                $slugify = new Slugify();
                $originalFilename = pathinfo($flagFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugify->slugify($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$flagFile->guessExtension();

                // Déplacer le fichier dans le répertoire spécifié
                try {
                    $flagFile->move(
                        $this->getParameter('pictures_directory'), // Assurez-vous que ce paramètre est défini dans votre config
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'exception si quelque chose se passe pendant l'upload
                }

                // Mettre à jour la propriété 'flagImage' pour stocker le nom du fichier
                $language->setFlagImage($newFilename);
            }

            // Enregistrer la langue
            $manager->persist($language);
            $manager->flush();

            $this->addFlash('success', 'La langue '.$language->getName().' a été créée avec succès.');

            return $this->redirectToRoute('admin_language_index');
        }

        return $this->render('admin/language/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/language/{id}/edit', name: 'admin_language_edit')]
    public function edit(Language $language, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(LanguageType::class, $language);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload du fichier
            $flagFile = $form->get('flagImage')->getData();
            if ($flagFile) {
                // Supprimer l'ancienne image s'il en existe une
                $oldFlagImage = $language->getFlagImage();
                if ($oldFlagImage) {
                    $oldFlagPath = $this->getParameter('pictures_directory').'/'.$oldFlagImage;
                    if (file_exists($oldFlagPath)) {
                        unlink($oldFlagPath);
                    }
                }

                $slugify = new Slugify();
                $originalFilename = pathinfo($flagFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugify->slugify($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$flagFile->guessExtension();

                // Déplacer le fichier dans le répertoire spécifié
                try {
                    $flagFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'exception si quelque chose se passe pendant l'upload
                }

                // Mettre à jour la propriété 'flagImage' pour stocker le nom du fichier
                $language->setFlagImage($newFilename);
            }

            // Mettre à jour la langue
            $manager->flush();

            $this->addFlash('success', 'La langue '.$language->getName().' a été modifiée avec succès.');

            return $this->redirectToRoute('admin_language_index');
        }

        return $this->render('admin/language/edit.html.twig', [
            'form' => $form->createView(),
            'language' => $language,
        ]);
    }

    #[Route('/admin/language/{id}/delete', name: 'admin_language_delete')]
    public function delete(Language $language, EntityManagerInterface $manager): Response
    {
        // Supprimer l'image associée s'il en existe une
        $flagImage = $language->getFlagImage();
        if ($flagImage) {
            $flagPath = $this->getParameter('flags_directory').'/'.$flagImage;
            if (file_exists($flagPath)) {
                unlink($flagPath);
            }
        }

        $manager->remove($language);
        $manager->flush();

        $this->addFlash('success', 'La langue '.$language->getName().' a été supprimée avec succès.');

        return $this->redirectToRoute('admin_language_index');
    }
}
