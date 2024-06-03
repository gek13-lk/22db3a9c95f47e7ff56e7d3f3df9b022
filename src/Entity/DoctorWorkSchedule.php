<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

//Расписание режима работы, не путать с результатом по кейсу!
#[ORM\Entity]
#[ORM\Table(name: 'doctor_work_schedules')]
class DoctorWorkSchedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Doctor::class, inversedBy: 'schedules')]
    private Doctor $doctor;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank]
    private \DateTime $startTime;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank]
    private \DateTime $endTime;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\Choice(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])]
    private string $dayOfWeek;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDoctors(): Doctor
    {
        return $this->doctor;
    }

    public function setDoctor(Doctor $doctor): self
    {
        $this->doctor = $doctor;

        return $this;
    }

    public function getStartTime(): \DateTime
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTime $startTime): self
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): \DateTime
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTime $endTime): self
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getDayOfWeek(): string
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(string $dayOfWeek): self
    {
        $this->dayOfWeek = $dayOfWeek;
        return $this;
    }
}
