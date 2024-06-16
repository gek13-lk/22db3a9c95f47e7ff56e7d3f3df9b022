<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TempScheduleWeekStudies
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: WeekStudies::class)]
    #[ORM\JoinColumn(nullable: true)]
    private WeekStudies $weekStudies;

    #[ORM\Column(type: 'integer')]
    private int $empty;

    #[ORM\ManyToOne(targetEntity: TempSchedule::class)]
    private TempSchedule $tempSchedule;

    #[ORM\ManyToOne(targetEntity: PredictedWeekStudies::class)]
    #[ORM\JoinColumn(nullable: true)]
    private PredictedWeekStudies $predicatedWeekStudies;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmpty(): int
    {
        return $this->empty;
    }

    public function setEmpty(int $empty): TempScheduleWeekStudies
    {
        $this->empty = $empty;
        return $this;
    }

    public function getWeekStudies(): WeekStudies
    {
        return $this->weekStudies;
    }

    public function setWeekStudies(WeekStudies $weekStudies): TempScheduleWeekStudies
    {
        $this->weekStudies = $weekStudies;
        return $this;
    }

    public function getTempSchedule(): TempSchedule
    {
        return $this->tempSchedule;
    }

    public function setTempSchedule(TempSchedule $tempSchedule): TempScheduleWeekStudies
    {
        $this->tempSchedule = $tempSchedule;
        return $this;
    }

    public function getPredicatedWeekStudies(): PredictedWeekStudies
    {
        return $this->predicatedWeekStudies;
    }

    public function setPredicatedWeekStudies(PredictedWeekStudies $predicatedWeekStudies): self
    {
        $this->predicatedWeekStudies = $predicatedWeekStudies;
        return $this;
    }
}