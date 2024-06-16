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
    private ?WeekStudies $weekStudies = null;

    #[ORM\Column(type: 'integer')]
    private int $empty;

    #[ORM\ManyToOne(targetEntity: TempSchedule::class)]
    private TempSchedule $tempSchedule;

    #[ORM\ManyToOne(targetEntity: PredictedWeekStudies::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?PredictedWeekStudies $predicatedWeekStudies = null;

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

    public function getTempSchedule(): TempSchedule
    {
        return $this->tempSchedule;
    }

    public function setTempSchedule(TempSchedule $tempSchedule): TempScheduleWeekStudies
    {
        $this->tempSchedule = $tempSchedule;
        return $this;
    }

    /**
     * @return WeekStudies|null
     */
    public function getWeekStudies(): ?WeekStudies
    {
        return $this->weekStudies;
    }

    /**
     * @param WeekStudies|null $weekStudies
     */
    public function setWeekStudies(?WeekStudies $weekStudies): void
    {
        $this->weekStudies = $weekStudies;
    }

    /**
     * @return PredictedWeekStudies|null
     */
    public function getPredicatedWeekStudies(): ?PredictedWeekStudies
    {
        return $this->predicatedWeekStudies;
    }

    /**
     * @param PredictedWeekStudies|null $predicatedWeekStudies
     */
    public function setPredicatedWeekStudies(?PredictedWeekStudies $predicatedWeekStudies): void
    {
        $this->predicatedWeekStudies = $predicatedWeekStudies;
    }
}