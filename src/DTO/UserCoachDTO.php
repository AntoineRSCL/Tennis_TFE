<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserCoachDTO
{
    #[Assert\NotBlank]
    private string $username;

    #[Assert\NotBlank]
    private string $password;

    #[Assert\NotBlank]
    private string $firstname;

    #[Assert\NotBlank]
    private string $lastname;

    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email;

    #[Assert\NotBlank]
    private string $phone;

    #[Assert\NotBlank]
    private string $ranking;

    #[Assert\NotBlank]
    private \DateTimeInterface $birthDate;

    private ?string $picture = null;

    #[Assert\NotBlank]
    private string $description;

    #[Assert\NotBlank]
    private string $languagesSpoken; // Assurez-vous d'utiliser ce nom

    // Getters and Setters
    public function getUsername(): string { return $this->username; }
    public function setUsername(string $username): self { $this->username = $username; return $this; }

    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }

    public function getFirstname(): string { return $this->firstname; }
    public function setFirstname(string $firstname): self { $this->firstname = $firstname; return $this; }

    public function getLastname(): string { return $this->lastname; }
    public function setLastname(string $lastname): self { $this->lastname = $lastname; return $this; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    public function getPhone(): string { return $this->phone; }
    public function setPhone(string $phone): self { $this->phone = $phone; return $this; }

    public function getRanking(): string { return $this->ranking; }
    public function setRanking(string $ranking): self { $this->ranking = $ranking; return $this; }

    public function getBirthDate(): \DateTimeInterface { return $this->birthDate; }
    public function setBirthDate(\DateTimeInterface $birthDate): self { $this->birthDate = $birthDate; return $this; }

    public function getPicture(): ?string { return $this->picture; }
    public function setPicture(?string $picture): self { $this->picture = $picture; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }

    public function getLanguagesSpoken(): string { return $this->languagesSpoken; } // Modifiez ici
    public function setLanguagesSpoken(string $languagesSpoken): self { $this->languagesSpoken = $languagesSpoken; return $this; }
}
