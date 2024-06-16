<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PredictedWeekStudies;
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
}
