<?php

namespace App\Form;

use App\DTO\UserCoachDTO;
use App\Entity\Language;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CoachType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class)
            ->add('password', PasswordType::class, [
                'required' => false,
            ])
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('email', EmailType::class)
            ->add('phone', TextType::class)
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
            ])
            ->add('birth_date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('picture', FileType::class, [
                'label' => 'Image',
                'required' => false,
                'mapped' => false,
            ])
            ->add('description', TextareaType::class)
            ->add('jobTitle', TextType::class)
            ->add('languages', EntityType::class, [
                'class' => Language::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
                'label' => 'Langues',
                'choices' => $options['languages'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserCoachDTO::class,
            'languages' => null,
        ]);
    }
}
