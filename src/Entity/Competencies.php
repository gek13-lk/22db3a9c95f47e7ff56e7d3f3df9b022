<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CompetenciesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetenciesRepository::class)]
class Competencies
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Модальность"])]
    private ?string $modality = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Контрастное усиление"])]
    private ?string $contrast = null;

    #[ORM\Column(type: 'float', nullable: true, options: ["comment" => "Минимальное количество исследований за смену шт. "])]
    private ?float $minimalCountPerShift = null;

    #[ORM\Column(type: 'float', nullable: true, options: ["comment" => "Минимальное количество УЕ за смену шт."])]
    private ?float $minimalCoefficientPerShift = null;

    #[ORM\Column(type: 'float', nullable: true, options: ["comment" => "Максимальное количество исследований за смену шт."])]
    private ?float $maxCountPerShift = null;

    #[ORM\Column(type: 'float', nullable: true, options: ["comment" => "Максимальное количество УЕ за смену, с округлением вниз до целого числа"])]
    private ?float $maxCoefficientPerShift = null;

    #[ORM\Column(type: 'float', nullable: true, options: ["comment" => "Количество УЕ в одном описании"])]
    private ?float $coefficient = null;

    public function getModality(): ?string
    {
        return $this->modality;
    }

    public function setModality(?string $modality): self
    {
        $this->modality = $modality;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoefficient(): ?float
    {
        return $this->coefficient;
    }

    public function setCoefficient(?float $coefficient): void
    {
        $this->coefficient = $coefficient;
    }

    public function getContrast(): ?string
    {
        return $this->contrast;
    }

    public function setContrast(?string $contrast): Competencies
    {
        $this->contrast = $contrast;
        return $this;
    }

    public function getMinimalCountPerShift(): ?float
    {
        return $this->minimalCountPerShift;
    }

    public function setMinimalCountPerShift(?float $minimalCountPerShift): Competencies
    {
        $this->minimalCountPerShift = $minimalCountPerShift;
        return $this;
    }

    public function getMinimalCoefficientPerShift(): ?float
    {
        return $this->minimalCoefficientPerShift;
    }

    public function setMinimalCoefficientPerShift(?float $minimalCoefficientPerShift): Competencies
    {
        $this->minimalCoefficientPerShift = $minimalCoefficientPerShift;
        return $this;
    }

    public function getMaxCountPerShift(): ?float
    {
        return $this->maxCountPerShift;
    }

    public function setMaxCountPerShift(?float $maxCountPerShift): Competencies
    {
        $this->maxCountPerShift = $maxCountPerShift;
        return $this;
    }

    public function getMaxCoefficientPerShift(): ?float
    {
        return $this->maxCoefficientPerShift;
    }

    public function setMaxCoefficientPerShift(?float $maxCoefficientPerShift): Competencies
    {
        $this->maxCoefficientPerShift = $maxCoefficientPerShift;
        return $this;
    }
}
