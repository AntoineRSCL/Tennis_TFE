<?php

namespace App\Form;

use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ResetPasswordType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('new_password', PasswordType::class, [
                'label' => 'Nouveau mot de passe',
                'attr' => [
                    'class' => 'inputLogin', // Ajouter la classe CSS
                    'placeholder' => 'Votre nouveau mot de passe',
                    'required' => 'required',
                ],
            ])
            ->add('confirm_password', PasswordType::class, [
                'label' => 'Confirmez le mot de passe',
                'attr' => [
                    'class' => 'inputLogin', // Ajouter la classe CSS
                    'placeholder' => 'Confirmez votre mot de passe',
                    'required' => 'required',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
