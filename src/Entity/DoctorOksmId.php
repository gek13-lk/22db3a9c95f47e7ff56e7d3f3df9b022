<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Справочник ФРНСИ «Общероссийский классификатор стран мира», OID 1.2.643.5.1.13.2.1.1.63
 */
#[ORM\Entity]
class DoctorOksmId
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;
    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Код"])]
    private ?int $code = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Наименование"])]
    private ?string $name = null;
    #[ORM\OneToOne(targetEntity: DoctorInfo::class, inversedBy: 'oksmId')]
    private DoctorInfo $doctorInfo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(?int $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDoctorInfo(): ?DoctorInfo
    {
        return $this->doctorInfo;
    }

    public function setDoctorInfo(?DoctorInfo $doctorInfo): static
    {
        $this->doctorInfo = $doctorInfo;

        return $this;
    }
}