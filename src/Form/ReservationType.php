<?php

namespace App\Form;

use App\Entity\Court;
use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startTime', null, [
                'widget' => 'single_text',
            ])
            ->add('endTime', null, [
                'widget' => 'single_text',
            ])
            ->add('player1', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('player2', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('court', EntityType::class, [
                'class' => Court::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
