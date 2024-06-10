<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class DoctorAddresses
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Идентификатор адреса"])]
    private ?string $addressId = null;
    #[ORM\Column(type: 'datetime', length: 255, nullable: true, options: ["comment" => "Дата регистрации"])]
    private ?\DateTime $regDate = null;
    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Справочник ФРНСИ «ФРМР. Тип адреса медицинского работника», OID 1.2.643.5.1.13.13.99.2.296"])]
    private ?int $addressTypeId = null;
    #[ORM\ManyToOne(targetEntity: DoctorInfo::class)]
    private DoctorInfo $doctorInfo;
    #[ORM\OneToOne(targetEntity: DoctorAddressesReg::class, mappedBy: 'doctorAddress')]
    private DoctorAddressesReg $doctorAddressesReg;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddressId(): ?string
    {
        return $this->addressId;
    }

    public function setAddressId(?string $addressId): static
    {
        $this->addressId = $addressId;

        return $this;
    }

    public function getRegDate(): ?\DateTimeInterface
    {
        return $this->regDate;
    }

    public function setRegDate(?\DateTimeInterface $regDate): static
    {
        $this->regDate = $regDate;

        return $this;
    }

    public function getAddressTypeId(): ?int
    {
        return $this->addressTypeId;
    }

    public function setAddressTypeId(?int $addressTypeId): static
    {
        $this->addressTypeId = $addressTypeId;

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

    public function getDoctorAddressesReg(): ?DoctorAddressesReg
    {
        return $this->doctorAddressesReg;
    }

    public function setDoctorAddressesReg(?DoctorAddressesReg $doctorAddressesReg): static
    {
        // unset the owning side of the relation if necessary
        if ($doctorAddressesReg === null && $this->doctorAddressesReg !== null) {
            $this->doctorAddressesReg->setDoctorAddress(null);
        }

        // set the owning side of the relation if necessary
        if ($doctorAddressesReg !== null && $doctorAddressesReg->getDoctorAddress() !== $this) {
            $doctorAddressesReg->setDoctorAddress($this);
        }

        $this->doctorAddressesReg = $doctorAddressesReg;

        return $this;
    }
}