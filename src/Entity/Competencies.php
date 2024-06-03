<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CompetenciesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetenciesRepository::class)]
class Competencies
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Норма в смену"])]
    private ?int $norms = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Модальность"])]
    private ?string $modality = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Вид исследования"])]
    private ?string $type = null;

    /**
     * @var Collection<Doctor>
     */
    #[ORM\ManyToMany(targetEntity: Doctor::class, mappedBy: "competencies")]
    private Collection $doctors;

    public function __construct() {
        $this->doctors = new ArrayCollection();
    }

    public function addDoctor(Doctor $doctor): self
    {
        if ($this->doctors->contains($doctor)) {
            return $this;
        }

        $this->doctors->add($doctor);
        $doctor->addSpeciality($this);

        return $this;
    }

    public function getNorms(): ?int
    {
        return $this->norms;
    }

    public function setNorms(?int $norms): self
    {
        $this->norms = $norms;

        return $this;
    }

    public function getModality(): ?string
    {
        return $this->modality;
    }

    public function setModality(?string $modality): self
    {
        $this->modality = $modality;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
