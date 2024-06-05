<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Calendar
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $calendarId = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Наименование"])]
    private ?string $rvd = null;

    #[ORM\Column(type: 'date', nullable: true, options: ["comment" => "Дата"])]
    private \DateTime|null $sqlDate = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Порядковый номер дня в текущем месяце"])]
    private ?int $dayOfMonth = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Порядковый номер дня в текущем году"])]
    private ?int $dayOfYear = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Порядковый номер недели в текущем году"])]
    private ?int $weekOfYear = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Порядковый номер месяца в текущем году"])]
    private ?int $monthOfYear = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true, options: ["comment" => "Название месяца"])]
    private ?string $monthName = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Год"])]
    private ?int $god = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Квартал"])]
    private ?int $quarter = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Полугодие"])]
    private ?int $halfYear = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Признак: 1- выходной/праздн.день, 0 -рабочий день"])]
    private ?int $holiday = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Порядковый номер дня в текущей недели"])]
    private ?int $dayOfWeek = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Признак: 1-последний день месяца, 0 -в остальных случаях"])]
    private ?int $endOfMonth = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $workDayOfYear = null;

    public function getCalendarId(): ?int
    {
        return $this->calendarId;
    }

    public function setCalendarId(?int $calendarId): Calendar
    {
        $this->calendarId = $calendarId;
        return $this;
    }

    public function getRvd(): ?string
    {
        return $this->rvd;
    }

    public function setRvd(?string $rvd): Calendar
    {
        $this->rvd = $rvd;
        return $this;
    }

    public function getContrast(): ?\DateTime
    {
        return $this->contrast;
    }

    public function setContrast(?\DateTime $contrast): Calendar
    {
        $this->contrast = $contrast;
        return $this;
    }

    public function getDayOfMonth(): ?int
    {
        return $this->dayOfMonth;
    }

    public function setDayOfMonth(?int $dayOfMonth): Calendar
    {
        $this->dayOfMonth = $dayOfMonth;
        return $this;
    }

    public function getDayOfYear(): ?int
    {
        return $this->dayOfYear;
    }

    public function setDayOfYear(?int $dayOfYear): Calendar
    {
        $this->dayOfYear = $dayOfYear;
        return $this;
    }

    public function getWeekOfYear(): ?int
    {
        return $this->weekOfYear;
    }

    public function setWeekOfYear(?int $weekOfYear): Calendar
    {
        $this->weekOfYear = $weekOfYear;
        return $this;
    }

    public function getMonthOfYear(): ?int
    {
        return $this->monthOfYear;
    }

    public function setMonthOfYear(?int $monthOfYear): Calendar
    {
        $this->monthOfYear = $monthOfYear;
        return $this;
    }

    public function getMonthName(): ?string
    {
        return $this->monthName;
    }

    public function setMonthName(?string $monthName): Calendar
    {
        $this->monthName = $monthName;
        return $this;
    }

    public function getGod(): ?int
    {
        return $this->god;
    }

    public function setGod(?int $god): Calendar
    {
        $this->god = $god;
        return $this;
    }

    public function getQuarter(): ?int
    {
        return $this->quarter;
    }

    public function setQuarter(?int $quarter): Calendar
    {
        $this->quarter = $quarter;
        return $this;
    }

    public function getHalfYear(): ?int
    {
        return $this->halfYear;
    }

    public function setHalfYear(?int $halfYear): Calendar
    {
        $this->halfYear = $halfYear;
        return $this;
    }

    public function getHoliday(): ?int
    {
        return $this->holiday;
    }

    public function setHoliday(?int $holiday): Calendar
    {
        $this->holiday = $holiday;
        return $this;
    }

    public function getDayOfWeek(): ?int
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(?int $dayOfWeek): Calendar
    {
        $this->dayOfWeek = $dayOfWeek;
        return $this;
    }

    public function getEndOfMonth(): ?int
    {
        return $this->endOfMonth;
    }

    public function setEndOfMonth(?int $endOfMonth): Calendar
    {
        $this->endOfMonth = $endOfMonth;
        return $this;
    }

    public function getWorkDayOfYear(): ?int
    {
        return $this->workDayOfYear;
    }

    public function setWorkDayOfYear(?int $workDayOfYear): Calendar
    {
        $this->workDayOfYear = $workDayOfYear;
        return $this;
    }

}