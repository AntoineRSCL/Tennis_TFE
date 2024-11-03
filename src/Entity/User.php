<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\LessThan;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "Le nom d'utilisateur est obligatoire")]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire")]
    #[Assert\Length(min: 5, minMessage:"Votre mot de passe doit faire plus de 5 caractères")]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire")]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire")]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse E-mail est obligatoire")]
    #[Assert\Email(message: "Le format de l'adresse E-mail doit être valide")]
    private ?string $email = null;

    #[ORM\Column(length: 25, nullable: true)]
    #[Assert\Length(max: 25, minMessage:"Votre numero de telephone doit faire moins de 25 caractères")]
    private ?string $phone = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "Le classement est obligatoire")]
    private ?string $ranking = null;

    #[ORM\Column(length: 5)]
    private ?string $double_ranking = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de naissance est obligatoire")]
    #[LessThan("today", message: "La date de naissance doit être antérieure à aujourd'hui.")]
    private ?\DateTimeInterface $birth_date = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Image(mimeTypes:['image/png','image/jpeg', 'image/jpg', 'image/gif', 'image/webp'], mimeTypesMessage:"Vous devez upload un fichier jpg, jpeg, webp, png ou gif")]
    #[Assert\File(maxSize:"2048k", maxSizeMessage: "La taille du fichier est trop grande")]
    private ?string $picture = null;

    #[ORM\Column]
    private ?bool $private = null;

    #[ORM\Column]
    private ?bool $address_verified = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'player1', orphanRemoval: true)]
    private Collection $reservations;

    /**
     * @var Collection<int, Tournament>
     */
    #[ORM\OneToMany(targetEntity: Tournament::class, mappedBy: 'winner')]
    private Collection $tournaments;

    /**
     * @var Collection<int, TournamentMatch>
     */
    #[ORM\OneToMany(targetEntity: TournamentMatch::class, mappedBy: 'player1', orphanRemoval: true)]
    private Collection $tournamentMatches;

    /**
     * @var Collection<int, TournamentRegistration>
     */
    #[ORM\OneToMany(targetEntity: TournamentRegistration::class, mappedBy: 'player', orphanRemoval: true)]
    private Collection $tournamentRegistrations;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Coach $coach = null;

    /**
     * @var Collection<int, AgendaReservation>
     */
    #[ORM\OneToMany(targetEntity: AgendaReservation::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $agendaReservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->tournaments = new ArrayCollection();
        $this->tournamentMatches = new ArrayCollection();
        $this->tournamentRegistrations = new ArrayCollection();
        $this->agendaReservations = new ArrayCollection();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function calculDoubleRanking(): void
    {
        // Vérifiez si le double_ranking doit être mis à jour
        $rankings = ['C30.6', 'C30.5', 'C30.4', 'C30.3', 'C30.2', 'C30.1', 'C30', 'C15.5', 'C15.4', 'C15.3', 'C15.2', 'C15.1', 'C15', 'B+4/6', 'B+2/6', 'B0', 'B-2/6', 'B-4/6', 'B-15', 'B-15.1', 'B-15.2', 'B-15.4', 'A.Nat', 'A.Int'];    
        $doubleRankings = [3, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100, 105, 110, 115];

        $index = array_search($this->ranking, $rankings);
        if ($index !== false) {
            $this->double_ranking = (string) $doubleRankings[$index];
        } else {
            $this->double_ranking = 3; // Valeur par défaut
        }
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setStatusVerified(): void 
    {
        if(empty($this->address_verified)) {
            $this->address_verified = false;
        }
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setRolesUser(): void 
    {
        if(empty($this->roles)) {
            $this->roles[] = 'ROLE_USER';
        }
    }

    /**
     * @return string Le nom complet de la personne (nom + prénom)
     */
    public function getFullName(): string
    {
        return $this->lastname . ' ' . $this->firstname;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getRanking(): ?string
    {
        return $this->ranking;
    }

    public function setRanking(string $ranking): static
    {
        $this->ranking = $ranking;

        return $this;
    }

    public function getDoubleRanking(): ?string
    {
        return $this->double_ranking;
    }

    public function setDoubleRanking(string $double_ranking): static
    {
        $this->double_ranking = $double_ranking;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birth_date;
    }

    public function setBirthDate(\DateTimeInterface $birth_date): static
    {
        $this->birth_date = $birth_date;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function isPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): static
    {
        $this->private = $private;

        return $this;
    }

    public function isAddressVerified(): ?bool
    {
        return $this->address_verified;
    }

    public function setAddressVerified(bool $address_verified): static
    {
        $this->address_verified = $address_verified;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setPlayer1($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getPlayer1() === $this) {
                $reservation->setPlayer1(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tournament>
     */
    public function getTournaments(): Collection
    {
        return $this->tournaments;
    }

    public function addTournament(Tournament $tournament): static
    {
        if (!$this->tournaments->contains($tournament)) {
            $this->tournaments->add($tournament);
            $tournament->setWinner($this);
        }

        return $this;
    }

    public function removeTournament(Tournament $tournament): static
    {
        if ($this->tournaments->removeElement($tournament)) {
            // set the owning side to null (unless already changed)
            if ($tournament->getWinner() === $this) {
                $tournament->setWinner(null);
            }
        }

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
            $tournamentMatch->setPlayer1($this);
        }

        return $this;
    }

    public function removeTournamentMatch(TournamentMatch $tournamentMatch): static
    {
        if ($this->tournamentMatches->removeElement($tournamentMatch)) {
            // set the owning side to null (unless already changed)
            if ($tournamentMatch->getPlayer1() === $this) {
                $tournamentMatch->setPlayer1(null);
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
            $tournamentRegistration->setPlayer($this);
        }

        return $this;
    }

    public function removeTournamentRegistration(TournamentRegistration $tournamentRegistration): static
    {
        if ($this->tournamentRegistrations->removeElement($tournamentRegistration)) {
            // set the owning side to null (unless already changed)
            if ($tournamentRegistration->getPlayer() === $this) {
                $tournamentRegistration->setPlayer(null);
            }
        }

        return $this;
    }

    public function getCoach(): ?Coach
    {
        return $this->coach;
    }

    public function setCoach(?Coach $coach): static
    {
        // unset the owning side of the relation if necessary
        if ($coach === null && $this->coach !== null) {
            $this->coach->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($coach !== null && $coach->getUser() !== $this) {
            $coach->setUser($this);
        }

        $this->coach = $coach;

        return $this;
    }

    /**
     * @return Collection<int, AgendaReservation>
     */
    public function getAgendaReservations(): Collection
    {
        return $this->agendaReservations;
    }

    public function addAgendaReservation(AgendaReservation $agendaReservation): static
    {
        if (!$this->agendaReservations->contains($agendaReservation)) {
            $this->agendaReservations->add($agendaReservation);
            $agendaReservation->setUser($this);
        }

        return $this;
    }

    public function removeAgendaReservation(AgendaReservation $agendaReservation): static
    {
        if ($this->agendaReservations->removeElement($agendaReservation)) {
            // set the owning side to null (unless already changed)
            if ($agendaReservation->getUser() === $this) {
                $agendaReservation->setUser(null);
            }
        }

        return $this;
    }
}
