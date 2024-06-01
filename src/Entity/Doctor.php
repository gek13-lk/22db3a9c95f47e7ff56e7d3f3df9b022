<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @var Collection<Competencies>
     */
    #[ORM\ManyToMany(targetEntity: Competencies::class, inversedBy: "doctors")]
    private Collection $competencies;

    /**
     * Норма выхода врача в смену.
     */
    #[ORM\OneToOne(targetEntity: DoctorWorkSchedule::class, mappedBy: 'doctor')]
    private DoctorWorkSchedule $workSchedule;

    public function __construct() {
        $this->competencies = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getFio();
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

    public function getCompetencies(): Collection
    {
        return $this->competencies;
    }

    public function addSpeciality(?Competencies $competencies = null): self
    {
        if (!$competencies) {
            return $this;
        }

        if ($this->competencies->contains($competencies)) {
            return $this;
        }

        $this->competencies->add($competencies);
        $competencies->addDoctor($this);

        return $this;
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
}
