<?php

namespace App\Entity;

use App\Repository\TournamentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TournamentRepository::class)]
class Tournament
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'tournaments')]
    private ?User $winner = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $rankingMin = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $rankingMax = null;

    #[ORM\Column(length: 20)]
    private ?string $participantsMax = null;

    /**
     * @var Collection<int, TournamentMatch>
     */
    #[ORM\OneToMany(targetEntity: TournamentMatch::class, mappedBy: 'tournament', orphanRemoval: true)]
    private Collection $tournamentMatches;

    /**
     * @var Collection<int, TournamentRegistration>
     */
    #[ORM\OneToMany(targetEntity: TournamentRegistration::class, mappedBy: 'tournament', orphanRemoval: true)]
    private Collection $tournamentRegistrations;

    #[ORM\Column]
    private ?int $currentRound = 0;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    public function __construct()
    {
        $this->tournamentMatches = new ArrayCollection();
        $this->tournamentRegistrations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getWinner(): ?User
    {
        return $this->winner;
    }

    public function setWinner(?User $winner): static
    {
        $this->winner = $winner;

        return $this;
    }

    public function getRankingMin(): ?string
    {
        return $this->rankingMin;
    }

    public function setRankingMin(?string $rankingMin): static
    {
        $this->rankingMin = $rankingMin;

        return $this;
    }

    public function getRankingMax(): ?string
    {
        return $this->rankingMax;
    }

    public function setRankingMax(?string $rankingMax): static
    {
        $this->rankingMax = $rankingMax;

        return $this;
    }

    public function getParticipantsMax(): ?string
    {
        return $this->participantsMax;
    }

    public function setParticipantsMax(string $participantsMax): static
    {
        $this->participantsMax = $participantsMax;

        return $this;
    }

    /**
     * @return Collection<int, TournamentMatch>
     */
    public function getTournamentMatches(): Collection
    {
        return $this->tournamentMatches;
    }

    public function addTournamentMatch(TournamentMatch $tournamentMatch): static
    {
        if (!$this->tournamentMatches->contains($tournamentMatch)) {
            $this->tournamentMatches->add($tournamentMatch);
            $tournamentMatch->setTournament($this);
        }

        return $this;
    }

    public function removeTournamentMatch(TournamentMatch $tournamentMatch): static
    {
        if ($this->tournamentMatches->removeElement($tournamentMatch)) {
            // set the owning side to null (unless already changed)
            if ($tournamentMatch->getTournament() === $this) {
                $tournamentMatch->setTournament(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TournamentRegistration>
     */
    public function getTournamentRegistrations(): Collection
    {
        return $this->tournamentRegistrations;
    }

    public function addTournamentRegistration(TournamentRegistration $tournamentRegistration): static
    {
        if (!$this->tournamentRegistrations->contains($tournamentRegistration)) {
            $this->tournamentRegistrations->add($tournamentRegistration);
            $tournamentRegistration->setTournament($this);
        }

        return $this;
    }

    public function removeTournamentRegistration(TournamentRegistration $tournamentRegistration): static
    {
        if ($this->tournamentRegistrations->removeElement($tournamentRegistration)) {
            // set the owning side to null (unless already changed)
            if ($tournamentRegistration->getTournament() === $this) {
                $tournamentRegistration->setTournament(null);
            }
        }

        return $this;
    }

    public function getCurrentRound(): ?int
    {
        return $this->currentRound;
    }

    public function setCurrentRound(int $currentRound): static
    {
        $this->currentRound = $currentRound;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }
}
