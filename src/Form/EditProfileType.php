<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\AbstractType;

class EditProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Informations de base
            ->add('username', TextType::class, [
                'label' => "Nom d'utilisateur",
                'attr' => ['placeholder' => "Votre nom d'utilisateur ..."]
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => "Votre prénom ..."]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom de famille',
                'attr' => ['placeholder' => "Votre nom de famille ..."]
            ])
            ->add('birth_date', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
            ])

            // Informations de contact
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => "Votre adresse e-mail ..."]
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['placeholder' => "Votre numéro de téléphone ..."]
            ])
            ->add('private', CheckboxType::class, [
                'label' => 'Apparaître dans le carnet d\'adresse des membres ?',
                'required' => false,
            ])

            // Classement
            ->add('ranking', ChoiceType::class, [
                'label' => 'Classement',
                'choices' => [
                    'C30.6' => 'C30.6', 'C30.5' => 'C30.5', 'C30.4' => 'C30.4', 
                    'C30.3' => 'C30.3', 'C30.2' => 'C30.2', 'C30.1' => 'C30.1', 
                    'C30' => 'C30', 'C15.5' => 'C15.5', 'C15.4' => 'C15.4', 
                    'C15.3' => 'C15.3', 'C15.2' => 'C15.2', 'C15.1' => 'C15.1', 
                    'C15' => 'C15', 'B+4/6' => 'B+4/6', 'B+2/6' => 'B+2/6', 
                    'B0' => 'B0', 'B-2/6' => 'B-2/6', 'B-4/6' => 'B-4/6', 
                    'B-15' => 'B-15', 'B-15.1' => 'B-15.1', 'B-15.2' => 'B-15.2', 
                    'B-15.4' => 'B-15.4', 'A.Nat' => 'A.Nat', 'A.Int' => 'A.Int'
                ],
                'expanded' => false,
                'multiple' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
