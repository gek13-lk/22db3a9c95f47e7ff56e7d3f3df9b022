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

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Максимальное количество врачей (входные данные)"])]
    private int $doctorsMaxCount;

    #[ORM\Column(name:'date', type: 'date', nullable: true, options: ["comment" => "Дата начала расписания"])]
    private \DateTimeInterface|null $date = null;

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

    public function getDoctorsMaxCount(): int
    {
        return $this->doctorsMaxCount;
    }

    public function setDoctorsMaxCount(int $doctorsMaxCount): TempSchedule
    {
        $this->doctorsMaxCount = $doctorsMaxCount;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): TempSchedule
    {
        $this->date = $date;
        return $this;
    }
}