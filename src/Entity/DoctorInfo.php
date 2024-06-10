<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class DoctorInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(type: 'boolean', options: ["comment" => "Признак наличия работника в ФРМР (это и медицинские и фармацевтические работники)", "default" => false])]
    private bool $mr = false;
    #[ORM\Column(type: 'boolean', options: ["comment" => "Признак обучающегося", "default" => false])]
    private bool $student = false;
    #[ORM\Column(type: 'boolean', options: ["comment" => "Признак медицинского работника - работники, которые трудоустроены на медицинских должностях в организации, имеющей лицензию на медицинскую деятельность, которые в тоже время имеют действующую аккредитацию/сертификат специалиста по медицинским специальностям", "default" => false])]
    private bool $isMedicalWorker = false;
    #[ORM\Column(type: 'boolean', options: ["comment" => "Признак фармацевтического работника - работники, которые трудоустроены на фармацевтических должностях в организации, имеющей лицензию на фармацевтическую деятельность, которые в тоже время имеют действующую аккредитацию/сертификат специалиста по фармацевтическим специальностям", "default" => false])]
    private bool $isPharmWorker = false;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "OID работника"])]
    private ?string $oid = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Фамилия"])]
    private ?string $lastName = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Имя"])]
    private ?string $firstName = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Отчество"])]
    private ?string $patronymic = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Пол (1 - мужской, 2 - женский)"])]
    private ?int $gender = null;
    #[ORM\Column(type: 'datetime', length: 255, nullable: true, options: ["comment" => "Дата рождения"])]
    private ?\DateTime $birthDate = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "СНИЛС сотрудника"])]
    private ?string $snils = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "ИНН сотрудника"])]
    private ?string $inn = null;
    #[ORM\OneToOne(targetEntity: DoctorCitizenShipId::class, mappedBy: 'doctorInfo')]
    private DoctorCitizenShipId $citizenShipId;
    #[ORM\OneToOne(targetEntity: DoctorOksmId::class, mappedBy: 'doctorInfo')]
    private DoctorOksmId $oksmId;
    #[ORM\Column(type: 'integer', nullable: true, options: ["comment" => "Отношение к военной службе"])]
    private ?int $militaryRelationId = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Номер телефона (+7)"])]
    private ?string $phone = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Адрес электронной почты"])]
    private ?string $email = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Данные о наличии инвалидности"])]
    private bool $isDisabled;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Группа инвалидности (Код)"])]
    private ?int $disabledGroupId = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Группа инвалидности (Наименование)"])]
    private ?string $disabledGroupName = null;
    #[ORM\Column(type: 'datetime', length: 255, nullable: true, options: ["comment" => "Дата начала инвалидности"])]
    private ?\DateTime $disabledDate = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ["comment" => "Идентификатор определяющий, что МР может быть дополнительно привлечен к оказанию медицинской помощи при угрозе распространения заболеваний, представляющих опасность для окружающих"])]
    private bool $covid19 = false;
    #[ORM\OneToMany(targetEntity: DoctorDocument::class, mappedBy: 'doctorInfo')]
    private array $doctorDocument = [];
    #[ORM\OneToMany(targetEntity: DoctorAddresses::class, mappedBy: 'doctorInfo')]
    private array $doctorAddress = [];

    public function __construct()
    {
        $this->doctorDocument = new ArrayCollection();
        $this->doctorAddress = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isMr(): ?bool
    {
        return $this->mr;
    }

    public function setMr(bool $mr): static
    {
        $this->mr = $mr;

        return $this;
    }

    public function isStudent(): ?bool
    {
        return $this->student;
    }

    public function setStudent(bool $student): static
    {
        $this->student = $student;

        return $this;
    }

    public function isMedicalWorker(): ?bool
    {
        return $this->isMedicalWorker;
    }

    public function setMedicalWorker(bool $isMedicalWorker): static
    {
        $this->isMedicalWorker = $isMedicalWorker;

        return $this;
    }

    public function isPharmWorker(): ?bool
    {
        return $this->isPharmWorker;
    }

    public function setPharmWorker(bool $isPharmWorker): static
    {
        $this->isPharmWorker = $isPharmWorker;

        return $this;
    }

    public function getOid(): ?string
    {
        return $this->oid;
    }

    public function setOid(?string $oid): static
    {
        $this->oid = $oid;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getPatronymic(): ?string
    {
        return $this->patronymic;
    }

    public function setPatronymic(?string $patronymic): static
    {
        $this->patronymic = $patronymic;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getSnils(): ?string
    {
        return $this->snils;
    }

    public function setSnils(?string $snils): static
    {
        $this->snils = $snils;

        return $this;
    }

    public function getInn(): ?string
    {
        return $this->inn;
    }

    public function setInn(?string $inn): static
    {
        $this->inn = $inn;

        return $this;
    }

    public function getMilitaryRelationId(): ?int
    {
        return $this->militaryRelationId;
    }

    public function setMilitaryRelationId(?int $militaryRelationId): static
    {
        $this->militaryRelationId = $militaryRelationId;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getIsDisabled(): ?string
    {
        return $this->isDisabled;
    }

    public function setIsDisabled(?string $isDisabled): static
    {
        $this->isDisabled = $isDisabled;

        return $this;
    }

    public function getDisabledGroupId(): ?string
    {
        return $this->disabledGroupId;
    }

    public function setDisabledGroupId(?string $disabledGroupId): static
    {
        $this->disabledGroupId = $disabledGroupId;

        return $this;
    }

    public function getDisabledGroupName(): ?string
    {
        return $this->disabledGroupName;
    }

    public function setDisabledGroupName(?string $disabledGroupName): static
    {
        $this->disabledGroupName = $disabledGroupName;

        return $this;
    }

    public function getDisabledDate(): ?\DateTimeInterface
    {
        return $this->disabledDate;
    }

    public function setDisabledDate(?\DateTimeInterface $disabledDate): static
    {
        $this->disabledDate = $disabledDate;

        return $this;
    }

    public function getCovid19(): ?string
    {
        return $this->covid19;
    }

    public function setCovid19(?string $covid19): static
    {
        $this->covid19 = $covid19;

        return $this;
    }

    public function getCitizenShipId(): ?DoctorCitizenShipId
    {
        return $this->citizenShipId;
    }

    public function setCitizenShipId(?DoctorCitizenShipId $citizenShipId): static
    {
        // unset the owning side of the relation if necessary
        if ($citizenShipId === null && $this->citizenShipId !== null) {
            $this->citizenShipId->setDoctorInfo(null);
        }

        // set the owning side of the relation if necessary
        if ($citizenShipId !== null && $citizenShipId->getDoctorInfo() !== $this) {
            $citizenShipId->setDoctorInfo($this);
        }

        $this->citizenShipId = $citizenShipId;

        return $this;
    }

    public function getOksmId(): ?DoctorOksmId
    {
        return $this->oksmId;
    }

    public function setOksmId(?DoctorOksmId $oksmId): static
    {
        // unset the owning side of the relation if necessary
        if ($oksmId === null && $this->oksmId !== null) {
            $this->oksmId->setDoctorInfo(null);
        }

        // set the owning side of the relation if necessary
        if ($oksmId !== null && $oksmId->getDoctorInfo() !== $this) {
            $oksmId->setDoctorInfo($this);
        }

        $this->oksmId = $oksmId;

        return $this;
    }

    /**
     * @return Collection<int, DoctorDocument>
     */
    public function getDoctorDocument(): Collection
    {
        return $this->doctorDocument;
    }

    public function addDoctorDocument(DoctorDocument $doctorDocument): static
    {
        if (!$this->doctorDocument->contains($doctorDocument)) {
            $this->doctorDocument->add($doctorDocument);
            $doctorDocument->setDoctorInfo($this);
        }

        return $this;
    }

    public function removeDoctorDocument(DoctorDocument $doctorDocument): static
    {
        if ($this->doctorDocument->removeElement($doctorDocument)) {
            // set the owning side to null (unless already changed)
            if ($doctorDocument->getDoctorInfo() === $this) {
                $doctorDocument->setDoctorInfo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DoctorAddresses>
     */
    public function getDoctorAddress(): Collection
    {
        return $this->doctorAddress;
    }

    public function addDoctorAddress(DoctorAddresses $doctorAddress): static
    {
        if (!$this->doctorAddress->contains($doctorAddress)) {
            $this->doctorAddress->add($doctorAddress);
            $doctorAddress->setDoctorInfo($this);
        }

        return $this;
    }

    public function removeDoctorAddress(DoctorAddresses $doctorAddress): static
    {
        if ($this->doctorAddress->removeElement($doctorAddress)) {
            // set the owning side to null (unless already changed)
            if ($doctorAddress->getDoctorInfo() === $this) {
                $doctorAddress->setDoctorInfo(null);
            }
        }

        return $this;
    }
}