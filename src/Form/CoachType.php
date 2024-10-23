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
            ->add('ranking', TextType::class)
            ->add('birth_date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('picture', FileType::class, [
                'label' => 'Image',
                'required' => false,
                'mapped' => false, // Ne pas mapper directement le champ 'picture' à l'entité News
            ])
            ->add('description', TextareaType::class)
            ->add('jobTitle', TextType::class)
            ->add('languages', EntityType::class, [
                'class' => Language::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
                'label' => 'Langues',
                'choices' => $options['languages'], // Utilisez les langues passées
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserCoachDTO::class,
            'languages' => null, // Ajoutez cette ligne pour définir 'languages'
        ]);
    }
}
