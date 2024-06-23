<?php

declare(strict_types=1);

namespace App\Controller\Api\model;

class DoctorModel
{
    /**
     * @var int|null Идентификатор
     */
    public ?int $id = null;

    /**
     * @var string|null ФИО
     */
    public ?string $fio = null;

    /**
     * @var string|null Компетенции
     */
    public ?array $competencies = null;

    /**
     * @var string|null Компетенции доп
     */
    public ?array $competenciesAddon = null;
}