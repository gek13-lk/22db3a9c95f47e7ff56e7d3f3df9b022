<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TempDoctorSchedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'date')]
    private \DateTime $date;

    #[ORM\ManyToOne(targetEntity: Doctor::class)]
    private Doctor $doctor;

    #[ORM\Column(type: 'string', nullable: true)]
    private string $startWorkTime;

    #[ORM\Column(type: 'string', nullable: true)]
    private string $endWorkTime;

    #[ORM\ManyToOne(targetEntity: TempScheduleWeekStudies::class)]
    private TempScheduleWeekStudies $tempSchedule;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): TempDoctorSchedule
    {
        $this->date = $date;
        return $this;
    }

    public function getDoctor(): Doctor
    {
        return $this->doctor;
    }

    public function setDoctor(Doctor $doctor): TempDoctorSchedule
    {
        $this->doctor = $doctor;
        return $this;
    }

    public function getStartWorkTime(): string
    {
        return $this->startWorkTime;
    }

    public function setStartWorkTime(string $startWorkTime): TempDoctorSchedule
    {
        $this->startWorkTime = $startWorkTime;
        return $this;
    }

    public function getEndWorkTime(): string
    {
        return $this->endWorkTime;
    }

    public function setEndWorkTime(string $endWorkTime): TempDoctorSchedule
    {
        $this->endWorkTime = $endWorkTime;
        return $this;
    }

    public function getTempSchedule(): TempScheduleWeekStudies
    {
        return $this->tempSchedule;
    }

    public function setTempSchedule(TempScheduleWeekStudies $tempSchedule): TempDoctorSchedule
    {
        $this->tempSchedule = $tempSchedule;

        return $this;
    }
}