<?php

namespace App\Controller;

use App\Entity\News;
use App\Form\NewsType;
use Cocur\Slugify\Slugify;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminNewsController extends AbstractController
{
    /**
     * Fonction pour afficher les actualités
     */
    #[Route('/admin/news/{page<\d+>?1}', name: 'admin_news_index')]
    public function index(PaginationService $pagination, int $page): Response
    {
        $pagination->setEntityClass(News::class)
                   ->setPage($page)
                   ->setLimit(9);

        return $this->render('admin/news/index.html.twig', [
            'pagination' => $pagination,       
        ]);
    }

    /**
     * Fonction pour créer une actualité
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/news/new', name: 'admin_news_new')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $news = new News();
        $form = $this->createForm(NewsType::class, $news);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $news->setCreatedAt(new \DateTime());

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
                $news->setPicture($newFilename);
            }

            $manager->persist($news);
            $manager->flush();

            $this->addFlash('success', 'La nouvelle '.$news->getId().' a été créée avec succès.');

            return $this->redirectToRoute('admin_news_index');
        }

        return $this->render('admin/news/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Fonction pour editer une nouvelle
     */
    #[Route('/admin/news/{id}/edit', name: 'admin_news_edit')]
    public function edit(News $news, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(NewsType::class, $news);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $pictureFile = $form->get('picture')->getData();
            if ($pictureFile) {
                // Supprimer l'ancienne image s'il en existe une
                $oldPicture = $news->getPicture();
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
                $news->setPicture($newFilename);
            }

            $manager->flush();

            $this->addFlash('success', 'La nouvelle '.$news->getId().' a été modifiée avec succès.');
            return $this->redirectToRoute('admin_news_index');
        }

        return $this->render('admin/news/edit.html.twig', [
            'form' => $form->createView(),
            'news' => $news,
        ]);
    }

    /**
     * Fonction pour supprimer une nouvelle
     */
    #[Route('/admin/news/{id}/delete', name: 'admin_news_delete')]
    public function delete(News $news, EntityManagerInterface $manager, Request $request): Response
    {
        // Supprimer l'image associée s'il en existe une
        $picture = $news->getPicture();
        if ($picture) {
            $picturePath = $this->getParameter('pictures_directory').'/'.$picture;
            if (file_exists($picturePath)) {
                unlink($picturePath);
            }
        }

        $this->addFlash(
            'success',
            'La nouvelle n°<strong>'.$news->getId().'</strong> a bien été supprimée'
        );
        $manager->remove($news);
        $manager->flush();

        return $this->redirectToRoute('admin_news_index');
    }
}
