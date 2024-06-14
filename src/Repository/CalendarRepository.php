<?php

namespace App\Repository;

use App\Entity\Calendar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\Persistence\ManagerRegistry;

class CalendarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Calendar::class);
    }

    /**
     * @return Calendar[]
     */
    public function getRange(\DateTime $dateStart, \DateTime $dateEnd): array
    {
        $qb =
            $this
            ->createQueryBuilder('d')
            ->andWhere('d.date BETWEEN :dateStart AND :dateEnd')
            ->setParameter('dateStart', $dateStart)
            ->setParameter('dateEnd', $dateEnd)
            ->orderBy('d.date', Order::Ascending->value);

        return $qb
            ->getQuery()
            ->getResult();
    }
}
