<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\Email;

class ResetPasswordController extends AbstractController
{
    #[Route('/reset-password', name: 'reset_password_request')]
    public function request(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $username = $form->get('username')->getData();
            $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

            if (!$user) {
                $this->addFlash('error', 'Utilisateur non trouvé.'); // Message utilisateur
                return $this->redirectToRoute('reset_password_request');
            }

            // Générer et enregistrer un token unique
            $token = bin2hex(random_bytes(32));
            $request->getSession()->set('reset_token', $token);

            // Créer l'email de réinitialisation
            $email = (new Email())
                ->from('contact@lesmashucclois.bautantoine.com')
                ->to($user->getEmail())
                ->subject('Réinitialisation de mot de passe')
                ->html('<p>Pour réinitialiser votre mot de passe, cliquez sur ce <a href="' . 
                $this->generateUrl('reset_password', ['token' => $token], true) . 
                '">lien</a>.</p>');

            $mailer->send($email);

            $this->addFlash('success', 'Un e-mail de réinitialisation a été envoyé.');
            return $this->redirectToRoute('login');
        }

        return $this->render('reset_password/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/{token}', name: 'reset_password')]
    public function reset(Request $request, string $token, EntityManagerInterface $entityManager): Response
    {
        $sessionToken = $request->getSession()->get('reset_token');

        if ($token !== $sessionToken) {
            throw $this->createNotFoundException('Token invalide');
        }

        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('new_password');
            $username = $request->request->get('username');
            $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

            if (!$user) {
                throw new UsernameNotFoundException('Utilisateur non trouvé');
            }

            $user->setPassword(password_hash($newPassword, PASSWORD_BCRYPT));
            $entityManager->flush();
            $request->getSession()->remove('reset_token');

            $this->addFlash('success', 'Mot de passe réinitialisé.');
            return $this->redirectToRoute('login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'token' => $token,
        ]);
    }
}
