<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TempScheduleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TempScheduleRepository::class)]
class TempSchedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $fitness = null;

    public function __construct() {
        $this->createdAt = new \DateTime();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): TempSchedule
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getFitness(): ?int
    {
        return $this->fitness;
    }

    public function setFitness(?int $fitness = null): TempSchedule
    {
        $this->fitness = $fitness;
        return $this;
    }
}