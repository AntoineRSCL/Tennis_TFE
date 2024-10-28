<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\ImgModify;
use App\Form\AccountType;
use Cocur\Slugify\Slugify;
use App\Form\ImgModifyType;
use App\Form\EditAvatarType;
use App\Entity\PasswordUpdate;
use App\Form\RegistrationType;
use App\Form\ChangePasswordType;
use App\Form\PasswordUpdateType;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;

class AccountController extends AbstractController
{
    /**
     * Fonction pour la connexion
     *
     * @param Request $request
     * @param AuthenticationUtils $utils
     * @return Response
     */
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

    /**
     * Fonction pour créer un compte
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordHasherInterface $passwordHasher
     * @return Response
     */
    #[Route('/register', name: 'account_register')]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Si l'utilisateur est déjà connecté, redirection vers l'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('myclub_index');
        }

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

    /**
     * Fonction pour modifer le profil
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/account/profile", name:"account_profile")]
    public function profile(Request $request, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser(); // Récupère l'utilisateur connecté
        
        $originalPicture = $user->getPicture(); // Récupère l'image existante

        // Ne tente de définir le fichier que si l'utilisateur a déjà une image
        if (!empty($originalPicture) && file_exists($this->getParameter('pictures_directory') . '/' . $originalPicture)) {
            $user->setPicture(new File($this->getParameter('pictures_directory') . '/' . $originalPicture));
        }

        $form = $this->createForm(AccountType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Réaffecte la même image à l'utilisateur si l'image n'a pas été modifiée
            $user->setPicture($originalPicture);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les données ont été enregistrées avec succès"
            );

            return $this->redirectToRoute('myclub_index');
        }

        return $this->render("account/profile.html.twig", [
            'form' => $form->createView()
        ]);
    }

    /**
     * Fonction pour modifier l'image de profil
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/account/imgmodify", name:"account_imgmodif")]
    #[IsGranted('ROLE_USER')]
    public function imgModify(Request $request, EntityManagerInterface $manager): Response
    {
        $imgModify = new ImgModify();
        $user = $this->getUser();
        $picture = $user->getPicture();
        $form = $this->createForm(ImgModifyType::class, $imgModify);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            //Permet de supprimer l'image dans le dossier
            if(!empty($user->getPicture()))
            {
                unlink($this->getParameter('pictures_directory').'/'.$user->getPicture());
            }

                // gestion de l'image
                $file = $form['newPicture']->getData();
                if(!empty($file))
                {
                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                    $newFilename = $safeFilename."-".uniqid().'.'.$file->guessExtension();
                    try{
                        $file->move(
                            $this->getParameter('pictures_directory'),
                            $newFilename
                        );
                    }catch(FileException $e)
                    {
                        return $e->getMessage();
                    }
                    $user->setPicture($newFilename);
                }
                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                'success',
                'Votre photo a bien été modifiée'
                );

                return $this->redirectToRoute('myclub_index');

        }

        return $this->render("account/imgModify.html.twig",[
            'myForm' => $form->createView(),
            'picture'=> $picture
        ]);
    }

    /**
     * Fonction pour supprimer l'image de profil
     *
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/account/delimg", name:"account_delimg")]
    #[IsGranted('ROLE_USER')]
    public function removeImg(EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        if(!empty($user->getPicture()))
        {
            unlink($this->getParameter('pictures_directory').'/'.$user->getPicture());
            $user->setPicture('');
            $manager->persist($user);
            $manager->flush();
            $this->addFlash(
                'success',
                'Votre photo de profil a bien été supprimée'
            );
        }

        
        return $this->redirectToRoute('myclub_index');

    }

    /**
     * Fonction pour modifier son mot de passe
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Route("/account/passwordupdate", name:"account_password")]
    #[IsGranted('ROLE_USER')]
    public function updatePassword(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $passwordUpdate = new PasswordUpdate();
        $user = $this->getUser();
        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // vérifier si le mot de passe correspond à l'ancien
            if(!password_verify($passwordUpdate->getOldPassword(),$user->getPassword()))
            {
                // gestion de l'erreur
                $form->get('oldPassword')->addError(new FormError("Le mot de passe que vous avez tapé n'est pas votre mot de passe actuel"));
            }else{
                $newPassword = $passwordUpdate->getNewPassword();
                $hash = $hasher->hashPassword($user, $newPassword);

                $user->setPassword($hash);
                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'Votre mot de passe a bien été modifié'
                );

                return $this->redirectToRoute('myclub_index');
            }

        }

        return $this->render("account/password.html.twig", [
            'myForm' => $form->createView()
        ]);

    }

    
}
