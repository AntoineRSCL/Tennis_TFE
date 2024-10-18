<?php

namespace App\Form;

use App\Entity\Contact;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastname', TextType::class, $this->getConfiguration('Nom', 'Nom'))
            ->add('firstName', TextType::class, $this->getConfiguration('Prénom', 'Prénom'))
            ->add('email', EmailType::class, $this->getConfiguration('Titre', 'Adresse mail'))
            ->add('subject', TextType::class, $this->getConfiguration('Sujet', 'Sujet'))
            ->add('message', TextareaType::class, $this->getConfiguration('Contenu', 'Votre message'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
