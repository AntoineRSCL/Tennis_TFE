<?php

namespace App\Controller;

use App\DTO\UserCoachDTO;
use App\Entity\User;
use App\Entity\Coach;
use App\Form\CoachType;
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
        $form = $this->createForm(CoachType::class, $dto);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $user = new User();
            $user->setUsername($dto->getUsername());
            $user->setRoles(['ROLE_COACH']);
            $user->setPrivate(true);
            $user->setAddressVerified(true);
            $user->setFirstname($dto->getFirstname()); // Utilisez le getter
            $user->setLastname($dto->getLastname());   // Utilisez le getter
            $user->setEmail($dto->getEmail());         // Utilisez le getter
            $user->setPhone($dto->getPhone());         // Utilisez le getter
            $user->setRanking($dto->getRanking());     // Utilisez le getter
            $user->setBirthDate($dto->getBirthDate()); // Utilisez le getter
    
            // Hash the password
            $hashedPassword = $passwordHasher->hashPassword($user, $dto->getPassword()); // Utilisez le getter
            $user->setPassword($hashedPassword);
    
            // Persist the User
            $entityManager->persist($user);
            $entityManager->flush();
    
            // Create the Coach
            $coach = new Coach();
            $coach->setUser($user);
            $coach->setDescription($dto->getDescription()); // Utilisez le getter
            $coach->setLanguagesSpoken($dto->getLanguagesSpoken()); // Utilisez le getter
    
            // Persist the Coach
            $entityManager->persist($coach);
            $entityManager->flush();
    
            return $this->redirectToRoute('admin_coach_index');
        }
    
        return $this->render('admin/coach/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/coach/{id}/edit', name: 'admin_coach_edit')]
    public function edit(Request $request, Coach $coach, EntityManagerInterface $entityManager): Response
    {
        // Créez un DTO à partir des données du coach
        $userCoachDTO = new UserCoachDTO();
        $userCoachDTO->setUsername($coach->getUser()->getUsername());
        $userCoachDTO->setFirstname($coach->getUser()->getFirstname());
        $userCoachDTO->setLastname($coach->getUser()->getLastname());
        $userCoachDTO->setEmail($coach->getUser()->getEmail());
        $userCoachDTO->setPhone($coach->getUser()->getPhone());
        $userCoachDTO->setRanking($coach->getUser()->getRanking());
        $userCoachDTO->setBirthDate($coach->getUser()->getBirthDate());
        $userCoachDTO->setDescription($coach->getDescription());
        $userCoachDTO->setLanguagesSpoken($coach->getLanguagesSpoken());

        $form = $this->createForm(CoachType::class, $userCoachDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettez à jour les entités
            $user = $coach->getUser();
            $user->setUsername($userCoachDTO->getUsername());
            $user->setFirstname($userCoachDTO->getFirstname());
            $user->setLastname($userCoachDTO->getLastname());
            $user->setEmail($userCoachDTO->getEmail());
            $user->setPhone($userCoachDTO->getPhone());
            $user->setRanking($userCoachDTO->getRanking());
            $user->setBirthDate($userCoachDTO->getBirthDate());
            $coach->setDescription($userCoachDTO->getDescription());
            $coach->setLanguagesSpoken($userCoachDTO->getLanguagesSpoken());

            $entityManager->flush();
            return $this->redirectToRoute('admin_coach_index');
        }

        return $this->render('admin/coach/edit.html.twig', [
            'form' => $form->createView(),
            'coach' => $coach,
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