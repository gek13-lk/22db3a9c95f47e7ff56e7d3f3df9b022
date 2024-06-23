<?php

declare(strict_types=1);

namespace App\Controller\Api\model;

class TempDoctorScheduleModel
{
    /**
     * @var int|null Идентификатор
     */
    public ?int $id = null;

    /**
     * @var DoctorModel|null Врач
     */
    public ?DoctorModel $doctor = null;

    /**
     * @var int|null Количество часов
     */
    public ?int $hours = null;

    /**
     * @var int|null Перерыв (мин)
     */
    public ?int $off = null;

    /**
     * @var int|null Предполагаемое количество исследований
     */
    public ?int $studiesCount = null;

    /**
     * @var int|null Предполагаемый коээфициент УЕ
     */
    public ?int $coefficient = null;

    /**
     * @var \DateTimeInterface|null Дата
     */
    public ?\DateTimeInterface $date = null;

    /**
     * @var \DateTimeInterface|null Начало смены
     */
    public ?\DateTimeInterface $start = null;

    /**
     * @var \DateTimeInterface|null Конец смены
     */
    public ?\DateTimeInterface $end = null;
}