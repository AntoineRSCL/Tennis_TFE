<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditAvatarType;
use App\Form\EditProfileType;
use App\Form\RegistrationType;
use App\Form\ChangePasswordType;
use Symfony\Component\Form\FormError;
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
    public function index(Request $request, AuthenticationUtils $utils): Response
    {
        // Si l'utilisateur est déjà connecté, redirection vers l'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('myclub_index');
        }

        // Récupérer l'erreur de connexion, le cas échéant
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();

        $loginError = null;
        if ($error) {
            $loginError = "Votre login ou mot de passe est incorrect";
        }

        // Récupérer l'URL de redirection depuis les paramètres GET ou définir une URL par défaut
        $redirectUrl = $request->query->get('redirect', $this->generateUrl('myclub_index'));

        return $this->render('account/index.html.twig', [
            'hasError' => $error !== null,
            'username' => $username,
            'loginError' => $loginError,
            'redirect' => $redirectUrl // Passer l'URL de redirection au formulaire de connexion
        ]);
    }


    /**
     * Fonction pour se deconnecter
     *
     * @return void
     */
    #[Route("/logout", name: "account_logout")]
    public function logout(): void
    {

    }

    #[Route('/register', name: 'account_register')]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifiez si le nom d'utilisateur existe déjà
            $existingUser = $em->getRepository(User::class)->findOneBy(['username' => $user->getUsername()]);
            if ($existingUser) {
                $form->addError(new FormError('Ce nom d\'utilisateur est déjà utilisé.'));
            } else {
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

                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('account_login'); // Redirigez l'utilisateur après l'inscription
            }
        }

        return $this->render('account/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/account/edit-avatar', name: 'account_edit_avatar')]
    public function editAvatar(Request $request, EntityManagerInterface $em, FileSystem $filesystem): Response
    {
        $user = $this->getUser(); // Récupère l'utilisateur connecté
        $form = $this->createForm(EditAvatarType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du fichier avatar
            $pictureFile = $form->get('picture')->getData();
            if ($pictureFile) {
                // Supprimer l'ancien fichier avatar s'il existe
                $oldPicture = $user->getPicture();
                if ($oldPicture) {
                    $filesystem->remove($this->getParameter('pictures_directory').'/'.$oldPicture);
                }

                // Générer un nouveau nom de fichier pour l'avatar
                $newFilename = uniqid().'.'.$pictureFile->guessExtension();
                
                // Déplacer le fichier vers le répertoire d'avatars
                $pictureFile->move(
                    $this->getParameter('pictures_directory'),
                    $newFilename
                );

                // Mettre à jour l'utilisateur avec le nouvel avatar
                $user->setPicture($newFilename);
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre avatar a bien été mis à jour.');

            return $this->redirectToRoute('account_edit_avatar'); // Redirection après modification
        }

        return $this->render('account/edit_avatar.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/account/change-password', name: 'account_change_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oldPassword = $form->get('old_password')->getData();
            if ($passwordHasher->isPasswordValid($user, $oldPassword)) {
                $newPassword = $form->get('new_password')->getData();
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
                $em->flush();
                $this->addFlash('success', 'Mot de passe modifié avec succès.');
            } else {
                $form->addError(new FormError('L\'ancien mot de passe est incorrect.'));
            }
        }

        return $this->render('account/change_password.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/account/edit-profile', name: 'account_edit_profile')]
    public function editProfile(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès.');
        }

        return $this->render('account/edit_profile.html.twig', ['form' => $form->createView()]);
    }
}
