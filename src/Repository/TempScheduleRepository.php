<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Doctor;
use App\Entity\TempDoctorSchedule;
use App\Entity\TempSchedule;
use App\Entity\TempScheduleWeekStudies;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

class TempScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TempSchedule::class);
    }

    public function getLastSchedule(): ?TempSchedule
    {
        return $this->findOneBy([], ['id' => 'DESC']);
    }
}
