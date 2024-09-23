<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 10)]
    private ?string $ranking = null;

    #[ORM\Column(length: 5)]
    private ?string $double_ranking = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $birth_date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[ORM\Column]
    private ?bool $private = null;

    #[ORM\Column]
    private ?bool $address_verified = null;

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function calculDoubleRanking(): void
    {
        if(empty($this->double_ranking)) {
            $rankings = ['C30.6', 'C30.5', 'C30.4', 'C30.3', 'C30.2', 'C30.1', 'C30', 'C15.5', 'C15.4', 'C15.3', 'C15.2', 'C15.1', 'C15', 'B+4/6', 'B+2/6', 'B0', 'B-2/6', 'B-4/6', 'B-15', 'B-15.1', 'B-15.2', 'B-15.4', 'A.Nat', 'A.Int'];    
            $doubleRankings = [3, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100, 105, 110, 115];

            $index = array_search($this->ranking, $rankings);
            if ($index !== false) {
                $this->double_ranking = (string) $doubleRankings[$index]; // Convertir en chaîne
            } else {
                $this->double_ranking = 3; // Ou une valeur par défaut
            }
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
}
