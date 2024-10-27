<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Coach;
use App\Form\CoachType;
use App\Entity\Language;
use App\DTO\UserCoachDTO;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminCoachController extends AbstractController
{
    #[Route('/admin/coach', name: 'admin_coach_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les coachs
        $coaches = $entityManager->getRepository(Coach::class)->findAll();

        return $this->render('admin/coach/index.html.twig', [
            'coaches' => $coaches,
        ]);
    }

    #[Route('/admin/coach/new', name: 'admin_coach_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $dto = new UserCoachDTO();
        $form = $this->createForm(CoachType::class, $dto, [
            'languages' => $entityManager->getRepository(Language::class)->findAll(),
        ]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Création du User
            $user = new User();
            $user->setUsername($dto->getUsername());
            $user->setRoles(['ROLE_COACH', 'ROLE_USER']);
            $user->setPrivate(true);
            $user->setAddressVerified(true);
            $user->setFirstname($dto->getFirstname());
            $user->setLastname($dto->getLastname());
            $user->setEmail($dto->getEmail());
            $user->setPhone($dto->getPhone());
            $user->setRanking($dto->getRanking());
            $user->setBirthDate($dto->getBirthDate());
    
            // Hash du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $dto->getPassword());
            $user->setPassword($hashedPassword);
    
            // Gestion de l'image
            $pictureFile = $form->get('picture')->getData();
            if ($pictureFile) {
                $slugify = new Slugify();
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugify->slugify($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();
    
                // Déplacer le fichier dans le répertoire approprié
                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'erreur d'upload si nécessaire
                }
    
                $user->setPicture($newFilename); // Mettre à jour l'entité User avec le nouveau nom de fichier
            }
    
            // Ajout du coach
            $coach = new Coach();
            $coach->setDescription($dto->getDescription());
            $coach->setJobTitle($dto->getJobTitle());
            $coach->setUser($user);
    
            // Ajout des langues
            foreach ($dto->getLanguages() as $language) {
                $coach->addLanguage($language);
            }

            // Persister l'entité User
            $entityManager->persist($user);
            // Persister l'entité Coach
            $entityManager->persist($coach);
            $entityManager->flush();
    
            return $this->redirectToRoute('admin_coach_index');
        }
    
        return $this->render('admin/coach/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/coach/{id}/edit', name: 'admin_coach_edit')]
    public function edit(Request $request, Coach $coach, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $dto = new UserCoachDTO();
        $dto->setUsername($coach->getUser()->getUsername());
        $dto->setFirstname($coach->getUser()->getFirstname());
        $dto->setLastname($coach->getUser()->getLastname());
        $dto->setEmail($coach->getUser()->getEmail());
        $dto->setPhone($coach->getUser()->getPhone());
        $dto->setRanking($coach->getUser()->getRanking());
        $dto->setBirthDate($coach->getUser()->getBirthDate());
        $dto->setDescription($coach->getDescription());
        $dto->setJobTitle($coach->getJobTitle());
        $dto->setLanguages($coach->getLanguages()->toArray()); // Utilisez toArray() pour passer les langues en tableau

        $form = $this->createForm(CoachType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour le coach
            $coach->setDescription($dto->getDescription());
            $coach->setJobTitle($dto->getJobTitle());

            // Supprimer les langues existantes
            $coach->removeAllLanguages();

            // Ajouter les nouvelles langues
            foreach ($dto->getLanguages() as $language) {
                // Trouver l'objet Language correspondant dans la base de données
                $languageEntity = $entityManager->getRepository(Language::class)->find($language->getId());
                if ($languageEntity) {
                    $coach->addLanguage($languageEntity);
                }
            }

            // Mettre à jour le mot de passe uniquement s'il est défini
            if ($dto->getPassword() !== null && $dto->getPassword() !== '') {
                $hashedPassword = $passwordHasher->hashPassword($coach->getUser(), $dto->getPassword());
                $coach->getUser()->setPassword($hashedPassword);
            }

            // Gestion de l'image
            $pictureFile = $form->get('picture')->getData();
            if ($pictureFile) {
                // Supprimer l'ancienne image
                $oldPicture = $coach->getUser()->getPicture();
                if ($oldPicture) {
                    $oldPicturePath = $this->getParameter('pictures_directory').'/'.$oldPicture;
                    if (file_exists($oldPicturePath)) {
                        unlink($oldPicturePath);
                    }
                }

                // Gérer le téléchargement de la nouvelle image
                $slugify = new Slugify();
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugify->slugify($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();
    
                // Déplacer le fichier dans le répertoire approprié
                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'erreur d'upload si nécessaire
                }
    
                $coach->getUser()->setPicture($newFilename); // Mettre à jour l'entité User avec le nouveau nom de fichier
            }

            $entityManager->flush();
            return $this->redirectToRoute('admin_coach_index');
        }

        return $this->render('admin/coach/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/coach/{id}/delete', name: 'admin_coach_delete', methods: ['POST'])]
    public function delete(Request $request, Coach $coach, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $coach->getId(), $request->request->get('_token'))) {
            $entityManager->remove($coach);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_coach_index');
    }
}
