<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DoctorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctorRepository::class)]
class Doctor {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist'])]
    private ?User $user = null;

    #[ORM\OneToOne(targetEntity: DoctorInfo::class, mappedBy: 'doctor', cascade: ['persist'])]
    private ?DoctorInfo $info = null;

    #[ORM\Column(type: 'json', options: ['jsonb' => true])]
    private array $addonCompetencies = [];

    #[ORM\Column(type: 'json', options: ['jsonb' => true])]
    private array $mainCompetencies = [];

    /**
     * Норма выхода врача в смену.
     */
    #[ORM\OneToOne(targetEntity: DoctorWorkSchedule::class, mappedBy: 'doctor', cascade: ['persist'])]
    private DoctorWorkSchedule $workSchedule;

    #[ORM\Column(type: 'float')]
    private ?float $stavka = null;

    public function __construct() {
        $this->info = new DoctorInfo($this);
        $this->workSchedule = new DoctorWorkSchedule();
        $this->workSchedule->setDoctor($this);
    }

    public function __toString() {
        return $this->getFio() ?? 'Doctor#' . $this->id;
    }

    public function getUser(): User {
        return $this->user ?? new User();
    }

    public function setUser(User $user): static {
        $this->user = $user;
        return $this;
    }

    public function getInfo(): DoctorInfo {
        return $this->info;
    }

    public function setInfo(DoctorInfo $info): static {
        $this->info = $info;
        return $this;
    }

    public function getMainCompetencies(): array {
        return $this->mainCompetencies;
    }

    public function setMainCompetencies(array $mainCompetencies): static {
        $this->mainCompetencies = $mainCompetencies;
        return $this;
    }

    public function getFio(): ?string {
        $names = [];

        if ($surName = $this->info->getLastName()) {
            $names[] = $surName;
        }

        if ($firstName = $this->info->getFirstName()) {
            $names[] = $firstName;
        }

        if ($middleName = $this->info->getPatronymic()) {
            $names[] = $middleName;
        }

        return empty($names) ? null : implode(' ', $names);
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getWorkSchedule(): ?DoctorWorkSchedule {
        return $this->workSchedule;
    }

    public function setWorkSchedule(DoctorWorkSchedule $workSchedule): void {
        $this->workSchedule = $workSchedule;
    }

    public function getStavka(): ?float {
        return $this->stavka;
    }

    public function setStavka(?float $stavka): Doctor {
        $this->stavka = $stavka;
        return $this;
    }

    public function getAddonCompetencies(): array {
        return $this->addonCompetencies;
    }

    public function setAddonCompetencies(array $addonCompetencies): Doctor {
        $this->addonCompetencies = $addonCompetencies;
        return $this;
    }

    public function getCompetency(): array {
        return $this->mainCompetencies;
    }

    public function setCompetency(array $competency): Doctor {
        $this->mainCompetencies = $competency;
        return $this;
    }
}
