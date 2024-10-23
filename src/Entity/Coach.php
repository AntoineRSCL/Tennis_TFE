<?php

namespace App\Entity;

use App\Repository\CoachRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoachRepository::class)]
class Coach
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $jobTitle = null; // Ajout du champ "fonction"

    #[ORM\ManyToMany(targetEntity: Language::class)]
    #[ORM\JoinTable(name: 'coach_language')]
    private Collection $languages; // Relation ManyToMany avec les langues

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $flagImage = null; // Stocke le chemin du fichier SVG du drapeau

    #[ORM\OneToOne(inversedBy: 'coach', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    public function __construct()
    {
        $this->languages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(string $jobTitle): self
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    /**
     * @return Collection|Language[]
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function addLanguage(Language $language): self
    {
        if (!$this->languages->contains($language)) {
            $this->languages[] = $language;
        }

        return $this;
    }

    public function removeLanguage(Language $language): self
    {
        $this->languages->removeElement($language);
        return $this;
    }

    // Nouvelle méthode pour supprimer toutes les langues
    public function removeAllLanguages(): void
    {
        $this->languages->clear(); // Supprime toutes les langues de la collection
    }

    public function getFlagImage(): ?string
    {
        return $this->flagImage;
    }

    public function setFlagImage(?string $flagImage): self
    {
        $this->flagImage = $flagImage;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
