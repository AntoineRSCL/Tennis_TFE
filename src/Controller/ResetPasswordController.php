<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Form\ResetPasswordType;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use App\Form\ResetPasswordRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class ResetPasswordController extends AbstractController
{
    #[Route('/reset-password', name: 'reset_password_request')]
    public function request(Request $request, UserRepository $userRepo, TokenGeneratorInterface $tokenGenerator, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $username = $form->get('username')->getData();
            $user = $userRepo->loadUserByUsername($username);

            if($user){
                // Générer un jeton unique
                $resetToken = $tokenGenerator->generateToken();

                // Stocker le token, l'email et la date d'expiration dans la session
                $request->getSession()->set('reset_token', $resetToken);
                $request->getSession()->set('reset_username', $username);

                // Définir une date d'expiration
                $expirationDate = (new \DateTime())->modify('+1 hour');
                $request->getSession()->set('reset_token_expiration', $expirationDate->format('Y-m-d H:i:s'));

                // Créer le lien de réinitialisation
                $resetUrl = $this->generateUrl('reset_password', ['token' => $resetToken], UrlGeneratorInterface::ABSOLUTE_URL);

                // Envoi de l'email
                $emailMessage = (new Email())
                 ->from('contact@lesmashucclois.bautantoine.com')
                 ->to($user->getEmail())
                 ->subject('Réinitialisation du mot de passe')
                 ->html("<p>Cliquez sur le lien suivant pour réinitialiser votre mot de passe : <a href='{$resetUrl}'>Réinitialiser mon mot de passe</a></p>");
         
                $mailer->send($emailMessage);
            
                $this->addFlash('success', 'Un email vous a été envoyé pour réinitialiser votre mot de passe.');
            } else {
                $this->addFlash('danger', 'Aucun utilisateur avec cette adresse mail.');
                return $this->redirectToRoute('reset_password_request');
            }
        }

        return $this->render('reset_password/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/{token}', name: 'reset_password')]
    public function reset(Request $request, UserRepository $userRepo, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher, string $token): Response
    {
        // Récupérer l'email et le token de la session
        $sessionToken = $request->getSession()->get('reset_token');
        $sessionUsername = $request->getSession()->get('reset_username');
        $expirationDate = $request->getSession()->get('reset_token_expiration');

        // Vérifie si le token correspond
        if ($sessionToken !== $token) {
            throw new AccessDeniedException('Token invalide ou utilisateur non trouvé.');
        }

        // Vérifier si le token est expiré
        if ($expirationDate && new DateTime() > new DateTime($expirationDate)) {
            $this->addFlash('error', 'Le lien de réinitialisation a expiré. Veuillez en demander un nouveau.');
            return $this->redirectToRoute('reset_password_request');
        }

        // Trouver l'utilisateur par l'username
        $user = $userRepo->loadUserByUsername($sessionUsername);

        if (!$user) {
            throw new AccessDeniedException('Utilisateur non trouvé.');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('new_password')->getData();
            $confirmPassword = $form->get('confirm_password')->getData();

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('danger', 'Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('reset_password', ['token' => $token]);
            }

            // Encoder le nouveau mot de passe
            $hash = $hasher->hashPassword($user, $newPassword);

            $user->setPassword($hash);
            $manager->persist($user);
            $manager->flush();

            // Supprimer les données de session
            $request->getSession()->remove('reset_token');
            $request->getSession()->remove('reset_email');

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
            return $this->redirectToRoute('account_login'); // rediriger vers la page de connexion
        }

        return $this->render('reset_password/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
