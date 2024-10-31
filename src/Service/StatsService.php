<?php 

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class  StatsService
{

    public function __construct(private EntityManagerInterface $manager)
    {}

    /**
     * Permet de récup le nombre d'utilisateur enregistré sur mon site
     *
     * @return integer|null
     */
    public function getUsersCount(): ?int
    {
        return $this->manager->createQuery("SELECT COUNT(u) FROM App\Entity\User u")->getSingleScalarResult();
    }

    /**
     * Permet de récup le nombre d'événements
     *
     * @return integer|null
     */
    public function getAgendaCount(): ?int
    {
        return $this->manager->createQuery("SELECT COUNT(a) FROM App\Entity\Agenda a")->getSingleScalarResult();
    }

    /**
     * Permet de récup le nombre d'événements
     *
     * @return integer|null
     */
    public function getNewsCount(): ?int
    {
        return $this->manager->createQuery("SELECT COUNT(n) FROM App\Entity\News n")->getSingleScalarResult();
    }

    /**
     * Permet de récup le nombre de coachs
     *
     * @return integer|null
     */
    public function getCoachCount(): ?int
    {
        return $this->manager->createQuery("SELECT COUNT(c) FROM App\Entity\Coach c")->getSingleScalarResult();
    }

    /**
     * Permet de récup le nombre de messages
     *
     * @return integer|null
     */
    public function getContactCount(): ?int
    {
        return $this->manager->createQuery("SELECT COUNT(c) FROM App\Entity\Contact c")->getSingleScalarResult();
    }

    /**
     * Permet de récup le nombre de terrains
     *
     * @return integer|null
     */
    public function getCourtCount(): ?int
    {
        return $this->manager->createQuery("SELECT COUNT(c) FROM App\Entity\Court c")->getSingleScalarResult();
    }

    /**
     * Permet de récup le nombre de langues
     *
     * @return integer|null
     */
    public function getLanguageCount(): ?int
    {
        return $this->manager->createQuery("SELECT COUNT(l) FROM App\Entity\Language l")->getSingleScalarResult();
    }

    /**
     * Permet de récup le nombre de sponsors
     *
     * @return integer|null
     */
    public function getSponsorCount(): ?int
    {
        return $this->manager->createQuery("SELECT COUNT(s) FROM App\Entity\Sponsor s")->getSingleScalarResult();
    }

    /**
     * Permet de récup le nombre de sponsors
     *
     * @return integer|null
     */
    public function getTournamentCount(): ?int
    {
        return $this->manager->createQuery("SELECT COUNT(t) FROM App\Entity\Tournament t")->getSingleScalarResult();
    }




}