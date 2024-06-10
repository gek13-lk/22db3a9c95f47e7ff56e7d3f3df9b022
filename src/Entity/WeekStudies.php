<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\WeekStudiesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WeekStudiesRepository::class)]
class WeekStudies
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Competencies::class)]
    private Competencies $competency;

    #[ORM\Column(type: 'integer')]
    private int $weekNumber;

    #[ORM\Column(type: 'integer')]
    private int $year;

    #[ORM\Column(type: 'integer')]
    private int $count;

    #[ORM\Column(type: 'date', nullable: true)]
    private \DateTime $startOfWeek;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isNew = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompetency(): Competencies
    {
        return $this->competency;
    }

    public function setCompetency(Competencies $competency): WeekStudies
    {
        $this->competency = $competency;
        return $this;
    }

    public function getWeekNumber(): int
    {
        return $this->weekNumber;
    }

    public function setWeekNumber(int $weekNumber): WeekStudies
    {
        $this->weekNumber = $weekNumber;
        return $this;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): WeekStudies
    {
        $this->year = $year;
        return $this;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): WeekStudies
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

    public function setStartOfWeek(\DateTime $startOfWeek): WeekStudies
    {
        $this->startOfWeek = $startOfWeek;
        return $this;
    }
}