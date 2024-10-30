<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Tournament;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class TournamentType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Tableau des classements à afficher avec les valeurs correspondantes
        $rankings = [
            'C30.6' => 3,
            'C30.5' => 5,
            'C30.4' => 10,
            'C30.3' => 15,
            'C30.2' => 20,
            'C30.1' => 25,
            'C30'   => 30,
            'C15.5' => 35,
            'C15.4' => 40,
            'C15.3' => 45,
            'C15.2' => 50,
            'C15.1' => 55,
            'C15'   => 60,
            'B+4/6' => 65,
            'B+2/6' => 70,
            'B0'    => 75,
            'B-2/6' => 80,
            'B-4/6' => 85,
            'B-15'  => 90,
            'B-15.1'=> 95,
            'B-15.2'=> 100,
            'B-15.4'=> 105,
            'A.Nat' => 110,
            'A.Int' => 115,
        ];

        $builder
            ->add('name', TextType::class, $this->getConfiguration("Nom du tournoi", "Entrez le nom du tournoi..."))
            ->add('rankingMin', ChoiceType::class, [
                'choices' => $rankings,
                'placeholder' => 'Sélectionnez le classement minimum',
                'required' => false,
            ])
            ->add('rankingMax', ChoiceType::class, [
                'choices' => $rankings,
                'placeholder' => 'Sélectionnez le classement maximum',
                'required' => false,
            ])
            ->add('participants_max', ChoiceType::class, [
                'choices' => [
                    4 => 4,
                    8 => 8,
                    16 => 16,
                    32 => 32,
                ],
            ])
            ->add('ageMin', IntegerType::class, [
                'required' => false,
                'label' => 'Âge minimum',
                'required' => false,
            ])
            ->add('ageMax', IntegerType::class, [
                'required' => false,
                'label' => 'Âge maximum',
                'required' => false,
            ])
            ->add('image', FileType::class, [
                'label' => 'Image du tournoi (JPEG, PNG)',
                'mapped' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tournament::class,
        ]);
    }
}
