<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\WeekStudies;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WeekStudiesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeekStudies::class);
    }

    /**
     * @return WeekStudies[]
     */
    public function getBetweenDates(
        string $from,
        string $to
    ): array
    {
        return
            $this
                ->createQueryBuilder('ws')
                ->andWhere('ws.startOfWeek >= :from')
                ->andWhere('ws.startOfWeek <= :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to)
                ->getQuery()
                ->getResult();
    }

    /**
     * @return WeekStudies[]
     */
    public function findByIds(
        array $ids,
        int $weekNumber
    ): array
    {
        return
            $this
                ->createQueryBuilder('ws')
                ->andWhere('ws.id IN (:arrayIds)')
                ->setParameter('arrayIds', $ids)
                ->andWhere('ws.week_number = :week')
                ->setParameter('week', $weekNumber)
            ->getQuery()
            ->getResult();
    }

    public function getAllWeekNumbers(string $from, string $to): array
    {
        return
            $this
                ->createQueryBuilder('ws')
                ->select('ws.weekNumber')
                ->andWhere('ws.startOfWeek >= :from')
                ->andWhere('ws.startOfWeek <= :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to)
                ->orderBy('ws.weekNumber', 'ASC')
                ->distinct()
                ->getQuery()
                ->getResult();
    }
}
