<?php

namespace App\Form;

use App\Entity\Agenda;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AgendaType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title', TextType::class, $this->getConfiguration("Titre", "Titre de l'evenement ..."))
        ->add('description', TextareaType::class, $this->getConfiguration("Description", "Description de l evenement ..."))
        ->add('picture', FileType::class, [
            'label' => 'Image',
            'required' => false,
            'mapped' => false, // Ne pas mapper directement le champ 'picture' à l'entité News
        ])
        ->add('date', DateType::class, $this->getConfiguration("Date de l'evenement", "Date de début de l'evenement",[
            "widget" => "single_text"
        ]))
        ->add('limitNumber', IntegerType::class, $this->getConfiguration("Nombre de participants", "Nombre limite de participants ...", [
            'required' => false, // Rendre le champ non obligatoire
        ]))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Agenda::class,
        ]);
    }
}
