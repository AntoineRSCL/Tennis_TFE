<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AgendaRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AgendaRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Agenda
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\Length(max: 150, maxMessage:"Le titre doit faire maximum 150 caractères")]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $picture = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(type: 'integer', message: 'Le nombre limite de participants doit être un entier.')]
    #[Assert\PositiveOrZero(message: 'Le nombre limite de participants doit être un nombre positif ou zéro.')]
    private ?int $limitNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    /**
     * Permet de créer un slug automatiquement à partir du titre de l'article
     *
     * @return void
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function initializeSlug(): void
    {
        if (empty($this->slug)) {
            $slugify = new Slugify();
            // Utiliser le titre de l'article suivi d'un identifiant unique
            $this->slug = $slugify->slugify($this->title . '-' . uniqid());
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getLimitNumber(): ?int
    {
        return $this->limitNumber;
    }

    public function setLimitNumber(?int $limitNumber): static
    {
        $this->limitNumber = $limitNumber;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }
}
