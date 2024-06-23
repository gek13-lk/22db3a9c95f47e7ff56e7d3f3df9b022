<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Competencies;
use App\Entity\PredictedWeekStudies;
use App\Enum\StudyType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PredictedWeekStudiesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PredictedWeekStudies::class);
    }

    public function getAllWeekNumbers(\DateTime $from, \DateTime $to): array
    {
        return
            $this
                ->createQueryBuilder('ws')
                ->select('ws.weekNumber, ws.year')
                ->andWhere('ws.startOfWeek >= :from')
                ->andWhere('ws.startOfWeek <= :to')
                ->andWhere('ws.isNew = true')
                ->setParameter('from', $from)
                ->setParameter('to', $to)
                ->orderBy('ws.weekNumber', 'ASC')
                ->distinct()
                ->getQuery()
                ->getResult();
    }

    public function getAllWeekNumbersEntity(\DateTime $from, \DateTime $to): array
    {
        return
            $this
                ->createQueryBuilder('ws')
                ->andWhere('ws.startOfWeek >= :from')
                ->andWhere('ws.startOfWeek <= :to')
                ->andWhere('ws.isNew = true')
                ->setParameter('from', $from)
                ->setParameter('to', $to)
                ->orderBy('ws.weekNumber', 'ASC')
                ->distinct()
                ->getQuery()
                ->getResult();
    }

    public function findByYearAndWeeks(int $year, array $weeks): array
    {
        return $this->createQueryBuilder('pws')
            ->andWhere('pws.year = :year')
            ->andWhere('pws.weekNumber IN (:weeks)')
            ->setParameter('year', $year)
            ->setParameter('weeks', $weeks)
            ->getQuery()
            ->getResult();
    }

    public function getByCompetency(StudyType $type, int $year): array
    {
        return $this->createQueryBuilder('ws')
            ->join(Competencies::class, 'c', 'WITH', 'ws.competency = c')
            ->where('c.code = :type')
            ->setParameter('type', $type->value)
            ->andWhere('ws.year = :year')
            ->setParameter('year', $year)
            ->getQuery()
            ->getResult();
    }
}
