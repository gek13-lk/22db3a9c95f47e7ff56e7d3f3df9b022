<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Doctor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Фамилия"])]
    private ?string $surname = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Имя"])]
    private ?string $firstname = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Отчество"])]
    private ?string $middlename = null;

    #[ORM\Column]
    private array $addonCompetencies = [];

    #[ORM\Column]
    private array $mainCompetencies = [];

    /**
     * Норма выхода врача в смену.
     */
    #[ORM\OneToOne(targetEntity: DoctorWorkSchedule::class, mappedBy: 'doctor')]
    private DoctorWorkSchedule $workSchedule;

    #[ORM\Column(type: 'float')]
    private ?float $stavka = null;

    public function __toString()
    {
        return $this->getFio() ?? 'Doctor#'.$this->id;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(?string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getMiddlename(): ?string
    {
        return $this->middlename;
    }

    public function setMiddlename(?string $middlename): self
    {
        $this->middlename = $middlename;

        return $this;
    }

    public function getFio(): ?string
    {
        $names = [];

        if ($surName = $this->getSurname()) {
            $names[] = $surName;
        }

        if ($firstName = $this->getFirstname()) {
            $names[] = $firstName;
        }

        if ($middleName = $this->getMiddlename()) {
            $names[] = $middleName;
        }

        return empty($names) ? null : implode(' ', $names);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorkSchedule(): DoctorWorkSchedule
    {
        return $this->workSchedule;
    }

    public function setWorkSchedule(DoctorWorkSchedule $workSchedule): void
    {
        $this->workSchedule = $workSchedule;
    }

    public function getStavka(): ?float
    {
        return $this->stavka;
    }

    public function setStavka(?float $stavka): Doctor
    {
        $this->stavka = $stavka;
        return $this;
    }

    public function getAddonCompetencies(): array
    {
        return $this->addonCompetencies;
    }

    public function setAddonCompetencies(array $addonCompetencies): Doctor
    {
        $this->addonCompetencies = $addonCompetencies;
        return $this;
    }

    public function getCompetency(): array
    {
        return $this->mainCompetencies;
    }

    public function setCompetency(array $competency): Doctor
    {
        $this->mainCompetencies = $competency;
        return $this;
    }
}
