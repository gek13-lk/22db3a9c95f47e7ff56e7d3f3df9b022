<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Doctor;
use App\Entity\TempDoctorSchedule;
use App\Entity\TempSchedule;
use App\Entity\TempScheduleWeekStudies;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class TempDoctorScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TempDoctorSchedule::class);
    }

    /**
     * @return TempDoctorSchedule[]
     */
    public function findByTempSchedule(TempSchedule $tempSchedule): array
    {
        return
            $this
            ->createQueryBuilder('tds')
                ->join(TempScheduleWeekStudies::class, 'tsws', Join::WITH, 'tds.tempScheduleWeekStudies = tsws.id')
->andWhere('tsws.tempSchedule = :tempSchedule')
                ->setParameter('tempSchedule', $tempSchedule)
            ->getQuery()
            ->getResult();
    }
}
