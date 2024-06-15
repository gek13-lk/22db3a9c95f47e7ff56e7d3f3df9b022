<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Выходные дни врача (отпросился, форс мажор и т.д.)
 */
#[ORM\Entity]
class OffDoctorDays
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Doctor::class)]
    private Doctor $doctor;

    #[ORM\Column(type: 'date')]
    private \DateTime $date;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Причина"])]
    private ?string $reason = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false, "comment" => "Утвержден руководителем"])]
    private bool $isApproved = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false, "comment" => "Это отпуск"])]
    private bool $vacation = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDoctor(): Doctor
    {
        return $this->doctor;
    }

    public function setDoctor(Doctor $doctor): OffDoctorDays
    {
        $this->doctor = $doctor;
        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): OffDoctorDays
    {
        $this->date = $date;
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): OffDoctorDays
    {
        $this->reason = $reason;
        return $this;
    }

    public function isApproved(): bool
    {
        return $this->isApproved;
    }

    public function setIsApproved(bool $isApproved): OffDoctorDays
    {
        $this->isApproved = $isApproved;
        return $this;
    }

    public function isVacation(): bool
    {
        return $this->vacation;
    }

    public function setVacation(bool $vacation): OffDoctorDays
    {
        $this->vacation = $vacation;
        return $this;
    }
}