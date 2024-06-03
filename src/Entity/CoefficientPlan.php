<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CoefficientPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'float', nullable: true, options: ["comment" => "Минимальный план  УЕ* в смену"])]
    private ?float $minimalCoefficientPerShift = null;

    #[ORM\Column(type: 'float', nullable: true, options: ["comment" => "Максимальный план  УЕ* в смену"])]
    private ?float $maxCoefficientPerShift = null;

    #[ORM\Column(type: 'float', nullable: true, options: ["comment" => "Минимальный план  УЕ* в месяц"])]
    private ?float $minimalCoefficientPerMonth = null;

    #[ORM\Column(type: 'float', nullable: true, options: ["comment" => "Максимальный план  УЕ* в месяц"])]
    private ?float $maxCoefficientPerMonth = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMinimalCoefficientPerShift(): ?float
    {
        return $this->minimalCoefficientPerShift;
    }

    public function setMinimalCoefficientPerShift(?float $minimalCoefficientPerShift): CoefficientPlan
    {
        $this->minimalCoefficientPerShift = $minimalCoefficientPerShift;
        return $this;
    }

    public function getMaxCoefficientPerShift(): ?float
    {
        return $this->maxCoefficientPerShift;
    }

    public function setMaxCoefficientPerShift(?float $maxCoefficientPerShift): CoefficientPlan
    {
        $this->maxCoefficientPerShift = $maxCoefficientPerShift;
        return $this;
    }

    public function getMinimalCoefficientPerMonth(): ?float
    {
        return $this->minimalCoefficientPerMonth;
    }

    public function setMinimalCoefficientPerMonth(?float $minimalCoefficientPerMonth): CoefficientPlan
    {
        $this->minimalCoefficientPerMonth = $minimalCoefficientPerMonth;
        return $this;
    }

    public function getMaxCoefficientPerMonth(): ?float
    {
        return $this->maxCoefficientPerMonth;
    }

    public function setMaxCoefficientPerMonth(?float $maxCoefficientPerMonth): CoefficientPlan
    {
        $this->maxCoefficientPerMonth = $maxCoefficientPerMonth;
        return $this;
    }
}