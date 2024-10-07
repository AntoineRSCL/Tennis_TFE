<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Tournament;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TournamentType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, $this->getConfiguration("Nom du tournoi", "Entrez le nom du tournoi..."))
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Inscription' => 'inscription',
                    'En cours' => 'en cours',
                    'Terminé' => 'termine',
                ]
            ])
            ->add('rankingMin', TextType::class, $this->getConfiguration("Ranking Minimum", "Entrez le classement minimum du tournoi si besoin...", [
                'required' => false
            ]))
            ->add('rankingMax', TextType::class, $this->getConfiguration("Ranking Maximum", "Entrez le classement maximum du tournoi si besoin...", [
                'required' => false
            ]))
            ->add('participants_max', ChoiceType::class, [
                'choices' => [
                    4 => 4,
                    8 => 8,
                    16 => 16,
                    32 => 32,
                ],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tournament::class,
        ]);
    }
}
