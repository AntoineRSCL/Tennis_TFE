<?php

namespace App\Form;

use App\Entity\User;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class AccountType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //Etape 1: Information de Connexion
            ->add('username', TextType::class, $this->getConfiguration("Nom d'utilisateur", "Votre nom d'utilisateur..."))

            //Etape 2: Informations Personnelles
            ->add('firstname', TextType::class, $this->getConfiguration("Prénom", "Votre prénom ..."))
            ->add('lastname', TextType::class, $this->getConfiguration("Nom", "Votre nom de famille ..."))
            ->add('birth_date', DateType::class, [
                'widget' => 'single_text',
            ])

            //Etape 3: Information de Contact
            ->add('email', EmailType::class, $this->getConfiguration("Email", "Votre adresse e-mail ..."))
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false, // Définir le champ comme non obligatoire
                'attr' => ['placeholder' => "Votre numéro de téléphone ..."] // Placeholder pour indiquer le format attendu
            ])
            ->add('private', CheckboxType::class, [
                'label' => 'Voulez-vous apparaître dans le carnet d\'adresse des membres ?',
                'required' => false,
            ])

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
                'expanded' => false, // Pour utiliser un menu déroulant
                'multiple' => false,  // Un seul choix
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
