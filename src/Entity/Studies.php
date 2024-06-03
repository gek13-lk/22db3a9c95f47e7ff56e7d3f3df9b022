<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Studies
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Competencies::class)]
    private Competencies $competency;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private \DateTime|null $date = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEndTime(): ?\DateTime
    {
        return (clone $this->date)->modify('+ '.$this->getCompetency()->getDuration().' minutes');
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): Studies
    {
        $this->date = $date;
        return $this;
    }

    public function getCompetency(): Competencies
    {
        return $this->competency;
    }

    public function setCompetency(Competencies $competency): Studies
    {
        $this->competency = $competency;
        return $this;
    }

    public function isNight(): bool
    {
        $startNight = (clone $this->date)->setTime(22, 0);

        return $this->date >= $startNight;
    }

    public function isDay(): bool
    {
        $startNight = (clone $this->date)->setTime(22, 0);
        $startDay = (clone $this->date)->setTime(6, 0);

        return $this->date <= $startNight && $this->date >= $startDay;
    }
}