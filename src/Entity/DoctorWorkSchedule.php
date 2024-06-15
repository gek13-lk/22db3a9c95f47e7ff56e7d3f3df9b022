<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

//Норма выхода врача в смену
#[ORM\Entity]
#[ORM\Table(name: 'doctor_work_schedules')]
class DoctorWorkSchedule
{
    public const TYPE_DAY = 'Дневные смены';
    public const TYPE_NIGHT = 'Ночные смены';
    public const TYPE_ONE_TO_THREE = 'Сутки через трое';
    public const TYPE_DAY_NIGHT = 'День-ночь';
    public const TYPE_TWO_OFF = 'Два выходных';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\OneToOne(targetEntity: Doctor::class, inversedBy: 'workSchedule')]
    private Doctor $doctor;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Тип смены"])]
    private ?string $type = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Количество часов за смену"])]
    private ?int $hoursPerShift = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Смен за цикл"])]
    private ?int $shiftPerCycle = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Количество выходных дней за цикл"])]
    private ?int $daysOff = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false, "comment" => "Выходные на выходных и праздниках"])]
    private bool $isHolidayOff = false;

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Желаемое время начала смены (час)"])]
    private ?int $shiftStartTimeHour = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Желаемое время начала смены (Минуты)"])]
    private ?int $shiftStartTimeMinutes = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDoctor(): Doctor
    {
        return $this->doctor;
    }

    public function setDoctor(Doctor $doctor): DoctorWorkSchedule
    {
        $this->doctor = $doctor;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): DoctorWorkSchedule
    {
        $this->type = $type;
        return $this;
    }

    public function getHoursPerShift(): ?int
    {
        return $this->hoursPerShift;
    }

    public function setHoursPerShift(?int $hoursPerShift): DoctorWorkSchedule
    {
        $this->hoursPerShift = $hoursPerShift;
        return $this;
    }

    public function getShiftPerCycle(): ?int
    {
        return $this->shiftPerCycle;
    }

    public function setShiftPerCycle(?int $shiftPerCycle): DoctorWorkSchedule
    {
        $this->shiftPerCycle = $shiftPerCycle;
        return $this;
    }

    public function getDaysOff(): ?int
    {
        return $this->daysOff;
    }

    public function setDaysOff(?int $daysOff): DoctorWorkSchedule
    {
        $this->daysOff = $daysOff;
        return $this;
    }

    public function isHolidayOff(): bool
    {
        return $this->isHolidayOff;
    }

    public function setIsHolidayOff(bool $isHolidayOff): DoctorWorkSchedule
    {
        $this->isHolidayOff = $isHolidayOff;
        return $this;
    }

    public function getShiftStartTimeHour(): ?int
    {
        return $this->shiftStartTimeHour;
    }

    public function setShiftStartTimeHour(?int $shiftStartTimeHour): DoctorWorkSchedule
    {
        $this->shiftStartTimeHour = $shiftStartTimeHour;
        return $this;
    }

    public function getShiftStartTimeMinutes(): ?int
    {
        return $this->shiftStartTimeMinutes;
    }

    public function setShiftStartTimeMinutes(?int $shiftStartTimeMinutes): DoctorWorkSchedule
    {
        $this->shiftStartTimeMinutes = $shiftStartTimeMinutes;
        return $this;
    }
}
