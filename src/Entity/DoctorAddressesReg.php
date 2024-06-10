<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class DoctorAddressesReg
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Идентификатор объекта адреса по ГАР"])]
    private ?string $gARguid = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Идентификатор улицы по ГАР"])]
    private ?string $aoidArea = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Идентификатор дома по ГАР"])]
    private ?string $houseid = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Код региона (id)"])]
    private ?int $region = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Наименование населенного пункта"])]
    private ?string $areaName = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Префикс населенного пункта"])]
    private ?string $prefixArea = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Наименование улицы"])]
    private ?string $streetName = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Префикс улицы"])]
    private ?string $prefixStreet = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Номер дома"])]
    private ?string $house = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Номер корпуса"])]
    private ?string $building = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Номер строения"])]
    private ?string $struct = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Номер квартиры"])]
    private ?string $flat = null;
    #[ORM\OneToOne(targetEntity: DoctorAddresses::class, inversedBy: 'doctorAddressesReg')]
    private DoctorAddresses $doctorAddress;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGARguid(): ?string
    {
        return $this->gARguid;
    }

    public function setGARguid(?string $gARguid): static
    {
        $this->gARguid = $gARguid;

        return $this;
    }

    public function getAoidArea(): ?string
    {
        return $this->aoidArea;
    }

    public function setAoidArea(?string $aoidArea): static
    {
        $this->aoidArea = $aoidArea;

        return $this;
    }

    public function getHouseid(): ?string
    {
        return $this->houseid;
    }

    public function setHouseid(?string $houseid): static
    {
        $this->houseid = $houseid;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getAreaName(): ?string
    {
        return $this->areaName;
    }

    public function setAreaName(?string $areaName): static
    {
        $this->areaName = $areaName;

        return $this;
    }

    public function getPrefixArea(): ?string
    {
        return $this->prefixArea;
    }

    public function setPrefixArea(?string $prefixArea): static
    {
        $this->prefixArea = $prefixArea;

        return $this;
    }

    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    public function setStreetName(?string $streetName): static
    {
        $this->streetName = $streetName;

        return $this;
    }

    public function getPrefixStreet(): ?string
    {
        return $this->prefixStreet;
    }

    public function setPrefixStreet(?string $prefixStreet): static
    {
        $this->prefixStreet = $prefixStreet;

        return $this;
    }

    public function getHouse(): ?string
    {
        return $this->house;
    }

    public function setHouse(?string $house): static
    {
        $this->house = $house;

        return $this;
    }

    public function getBuilding(): ?string
    {
        return $this->building;
    }

    public function setBuilding(?string $building): static
    {
        $this->building = $building;

        return $this;
    }

    public function getStruct(): ?string
    {
        return $this->struct;
    }

    public function setStruct(?string $struct): static
    {
        $this->struct = $struct;

        return $this;
    }

    public function getFlat(): ?string
    {
        return $this->flat;
    }

    public function setFlat(?string $flat): static
    {
        $this->flat = $flat;

        return $this;
    }

    public function getDoctorAddress(): ?DoctorAddresses
    {
        return $this->doctorAddress;
    }

    public function setDoctorAddress(?DoctorAddresses $doctorAddress): static
    {
        $this->doctorAddress = $doctorAddress;

        return $this;
    }
}