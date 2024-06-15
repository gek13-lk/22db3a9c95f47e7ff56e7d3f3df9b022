<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class DoctorDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Серия документа, удостоверяющего личность"])]
    private ?string $serial = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Номер документа, удостоверяющего личность"])]
    private ?string $number = null;
    #[ORM\Column(type: 'datetime', length: 255, nullable: true, options: ["comment" => "Дата выдачи документа, удостоверяющего личность"])]
    private ?\DateTime $passDate = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Организация выдачи документа, удостоверяющего личность"])]
    private ?string $passOrg = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Код подразделения"])]
    private ?string $codeOrg = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Код документа, удостоверяющего личность"])]
    private ?int $documentId = null;
    #[ORM\ManyToOne(targetEntity: DoctorInfo::class, inversedBy: 'doctorDocument')]
    private DoctorInfo $doctorInfo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSerial(): ?string
    {
        return $this->serial;
    }

    public function setSerial(?string $serial): static
    {
        $this->serial = $serial;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getPassDate(): ?\DateTimeInterface
    {
        return $this->passDate;
    }

    public function setPassDate(?\DateTimeInterface $passDate): static
    {
        $this->passDate = $passDate;

        return $this;
    }

    public function getPassOrg(): ?string
    {
        return $this->passOrg;
    }

    public function setPassOrg(?string $passOrg): static
    {
        $this->passOrg = $passOrg;

        return $this;
    }

    public function getCodeOrg(): ?string
    {
        return $this->codeOrg;
    }

    public function setCodeOrg(?string $codeOrg): static
    {
        $this->codeOrg = $codeOrg;

        return $this;
    }

    public function getDocumentId(): ?string
    {
        return $this->documentId;
    }

    public function setDocumentId(?string $documentId): static
    {
        $this->documentId = $documentId;

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