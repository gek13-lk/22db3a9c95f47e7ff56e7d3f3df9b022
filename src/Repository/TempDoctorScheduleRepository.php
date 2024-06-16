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

class TempDoctorScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TempDoctorSchedule::class);
    }

    /**
     * @return TempDoctorSchedule[]
     */
    public function findByTempScheduleAndDoctor(int $tempScheduleId, int $doctorId): array
    {
        return
            $this
            ->createQueryBuilder('tds')
                ->join(Doctor::class, 'd', Join::WITH, 'tds.doctor = d')
                ->join(TempScheduleWeekStudies::class, 'tsws', Join::WITH, 'tds.tempScheduleWeekStudies = tsws')
                ->join(TempSchedule::class, 'ts', Join::WITH, 'tsws.tempSchedule = ts')
                ->andWhere('ts.id = :id')
                ->andWhere('d.id = :doctorId')
                ->setParameter('id', $tempScheduleId)
                ->setParameter('doctorId', $doctorId)
            ->getQuery()
            ->getResult();
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
