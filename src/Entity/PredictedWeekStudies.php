<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PredictedWeekStudiesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'week_studies_predicted')]
#[ORM\Entity(repositoryClass: PredictedWeekStudiesRepository::class)]
class PredictedWeekStudies
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Competencies::class)]
    private Competencies $competency;

    #[ORM\Column(type: 'integer')]
    private int $weekNumber;

    #[ORM\Column(type: 'integer')]
    private int $year;

    #[ORM\Column(type: 'integer')]
    private int $count;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isNew = true;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime $startOfWeek = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompetency(): Competencies
    {
        return $this->competency;
    }

    public function setCompetency(Competencies $competency): PredictedWeekStudies
    {
        $this->competency = $competency;

        return $this;
    }

    public function getWeekNumber(): int
    {
        return $this->weekNumber;
    }

    public function setWeekNumber(int $weekNumber): PredictedWeekStudies
    {
        $this->weekNumber = $weekNumber;

        return $this;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): PredictedWeekStudies
    {
        $this->year = $year;

        return $this;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): PredictedWeekStudies
    {
        $this->count = $count;

        return $this;
    }

    public function isNew(): bool
    {
        return $this->isNew;
    }

    public function setIsNew(bool $new = true): self
    {
        $this->isNew = $new;

        return $this;
    }

    public function setIsNotNew(): self
    {
        $this->setIsNew(false);

        return $this;
    }

    public function getStartOfWeek(): \DateTime
    {
        return $this->startOfWeek;
    }

    public function setStartOfWeek(\DateTime $startOfWeek): self
    {
        $this->startOfWeek = $startOfWeek;

        return $this;
    }
}
