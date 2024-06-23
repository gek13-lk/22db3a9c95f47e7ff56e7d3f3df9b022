<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TempDoctorScheduleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TempDoctorScheduleRepository::class)]
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

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $workTimeStart = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $workTimeEnd = null;

    #[ORM\ManyToOne(targetEntity: TempScheduleWeekStudies::class)]
    private TempScheduleWeekStudies $tempScheduleWeekStudies;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $offMinutes = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $workHours = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Планируемое количество исследований"])]
    private ?int $studyCount = null;

    #[ORM\Column(type: 'float', nullable: true, options: ["comment" => "Планируемый коэффициент УЕ"])]
    private ?float $coefficient = null;

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

    public function getWorkTimeStart(): \DateTime|null
    {
        return $this->workTimeStart;
    }

    public function setWorkTimeStart(?\DateTime $workTimeStart = null): TempDoctorSchedule
    {
        $this->workTimeStart = $workTimeStart;
        return $this;
    }

    public function getWorkTimeEnd(): \DateTime|null
    {
        return $this->workTimeEnd;
    }

    public function setWorkTimeEnd(?\DateTime $workTimeEnd = null): TempDoctorSchedule
    {
        $this->workTimeEnd = $workTimeEnd;
        return $this;
    }

    public function getTempScheduleWeekStudies(): TempScheduleWeekStudies
    {
        return $this->tempScheduleWeekStudies;
    }

    public function setTempScheduleWeekStudies(TempScheduleWeekStudies $tempScheduleWeekStudies): TempDoctorSchedule
    {
        $this->tempScheduleWeekStudies = $tempScheduleWeekStudies;

        return $this;
    }

    public function getOffMinutes(): ?int
    {
        return $this->offMinutes;
    }

    public function setOffMinutes(?int $offMinutes): TempDoctorSchedule
    {
        $this->offMinutes = $offMinutes;
        return $this;
    }

    public function getWorkHours(): ?float
    {
        return $this->workHours;
    }

    public function setWorkHours(?float $workHours): TempDoctorSchedule
    {
        $this->workHours = $workHours;
        return $this;
    }

    public function getStudyCount(): ?int
    {
        return $this->studyCount;
    }

    public function setStudyCount(?int $studyCount): TempDoctorSchedule
    {
        $this->studyCount = $studyCount;
        return $this;
    }

    public function getCoefficient(): ?float
    {
        return $this->coefficient;
    }

    public function setCoefficient(?float $coefficient): TempDoctorSchedule
    {
        $this->coefficient = $coefficient;
        return $this;
    }
}