<?php

declare(strict_types=1);

namespace App\Controller\Api\model;

class TempScheduleModel
{
    /**
     * @var int|null Идентификатор
     */
    public ?int $id = null;

    /**
     * @var float|null Негативная оценка расписания
     */
    public ?float $fitness = null;

    /**
     * @var int|null Ограничение по количество докторов (входной параметр)
     */
    public ?int $maxDoctorsCount = null;

    /**
     * @var \DateTimeInterface|null Дата, на которую формируют расписание
     */
    public ?\DateTimeInterface $date = null;

    /**
     * @var \DateTimeInterface|null Дата создания
     */
    public ?\DateTimeInterface $createdAt = null;

    /**
     * @var bool|null Утверждено ли
     */
    public ?bool $isApproved = null;

    /**
     * @var TempScheduleStudiesModel[] Исследования по неделям
     */
    public array $tempScheduleStudies = [];
}