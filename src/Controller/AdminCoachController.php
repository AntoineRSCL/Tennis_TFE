<?php

namespace App\Controller;

use App\DTO\UserCoachDTO;
use App\Entity\User;
use App\Entity\Coach;
use App\Form\CoachType;
use App\Entity\Language;
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
            'languages' => $entityManager->getRepository(Language::class)->findAll(), // Récupérer toutes les langues
        ]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Création du User
            $user = new User();
            $user->setUsername($dto->getUsername());
            $user->setRoles(['ROLE_COACH']);
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
            $coach->removeAllLanguages(); // Supprime toutes les langues existantes

            // Ajouter les nouvelles langues
            foreach ($dto->getLanguages() as $language) {
                // Trouver l'objet Language correspondant dans la base de données
                $languageEntity = $entityManager->getRepository(Language::class)->find($language->getId());
                if ($languageEntity) {
                    $coach->addLanguage($languageEntity); // Ajoute l'objet Language à Coach
                }
            }

            // Mettre à jour le mot de passe uniquement s'il est défini
            if ($dto->getPassword() !== null && $dto->getPassword() !== '') {
                $hashedPassword = $passwordHasher->hashPassword($coach->getUser(), $dto->getPassword());
                $coach->getUser()->setPassword($hashedPassword);
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
