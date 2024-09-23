<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;

class AccountController extends AbstractController
{
    #[Route('/login', name: 'account_login')]
    public function index(AuthenticationUtils $utils): Response
    {
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();

        $loginError = null;

        if($error instanceof TooManyLoginAttemptsAuthenticationException)
        {
            // l'ereur est due à la limitation de tentative de connexion
            $loginError = "Trop de tentatives de connexion. Réessayez plus tard";
        }
        return $this->render('account/index.html.twig', [
            'hasError' => $error !== null,
            'username' => $username,
            'loginError' => $loginError
        ]);
    }

    #[Route('/register', name: 'account_register')]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the password
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            // Handle file upload (picture)
            $pictureFile = $form->get('picture')->getData();
            if ($pictureFile) {
                $newFilename = uniqid().'.'.$pictureFile->guessExtension();
                $pictureFile->move(
                    $this->getParameter('pictures_directory'), // Directory to store files
                    $newFilename
                );
                $user->setPicture($newFilename);
            }

            // Persist user to database
            $entityManager->persist($user);
            $entityManager->flush();

            // Optionally, redirect to login or another page
            return $this->redirectToRoute('account_login');
        }

        return $this->render('account/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
