<?php

declare(strict_types=1);

namespace App\Modules\Algorithm;

use App\Entity\TempSchedule;
use Doctrine\ORM\EntityManagerInterface;

class SetTimeAlgorithmService
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function setTime(TempSchedule $tempSchedule): void
    {

    }
}
