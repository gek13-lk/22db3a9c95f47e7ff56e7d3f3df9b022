<?php

namespace App\Controller\Api\model;

class TempScheduleStudiesModel
{
    /**
     * @var int|null Идентификатор
     */
    public ?int $id = null;

    /**
     * @var int|null Количество неназначенных исследований
     */
    public ?int $empty = null;

    /**
     * @var int|null Количество исследований
     */
    public ?int $studiesCount = null;

    /**
     * @var string|null Модальность
     */
    public ?string $modality = null;

    /**
     * @var \DateTime|null Дата начала недели
     */
    public ?\DateTime $startWeek = null;

    /**
     * @var int|null Номер недели
     */
    public ?int $weekNumber = null;

    /**
     * @var int|null Год
     */
    public ?int $year = null;
}