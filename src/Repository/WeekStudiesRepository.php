<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Competencies;
use App\Entity\WeekStudies;
use App\Enum\StudyType;
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

    public function getStructuredData(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
        SELECT aggregated_data.year, aggregated_data.week_number, aggregated_data.is_new,
            COALESCE(
                JSON_AGG(
                    JSON_BUILD_OBJECT('id', aggregated_data.id, 'count', aggregated_data.ct_count)
                ) FILTER (WHERE aggregated_data.ct_count > 0),
            '[]'::json) AS ct,
            COALESCE(
                JSON_AGG(
                    JSON_BUILD_OBJECT('id', aggregated_data.id, 'count', aggregated_data.mri_count)
                ) FILTER (WHERE aggregated_data.mri_count > 0),
            '[]'::json) AS mri,
            COALESCE(
                JSON_AGG(
                    JSON_BUILD_OBJECT('id', aggregated_data.id, 'count', aggregated_data.rg_count)
                ) FILTER (WHERE aggregated_data.rg_count > 0),
            '[]'::json) AS rg,
            COALESCE(
                JSON_AGG(
                    JSON_BUILD_OBJECT('id', aggregated_data.id, 'count', aggregated_data.flg_count)
                ) FILTER (WHERE aggregated_data.flg_count > 0),
            '[]'::json) AS flg,
            COALESCE(
                JSON_AGG(
                    JSON_BUILD_OBJECT('id', aggregated_data.id, 'count', aggregated_data.mmg_count)
                ) FILTER (WHERE aggregated_data.mmg_count > 0),
            '[]'::json) AS mmg,
            COALESCE(
                JSON_AGG(
                    JSON_BUILD_OBJECT('id', aggregated_data.id, 'count', aggregated_data.densitometer_count)
                ) FILTER (WHERE aggregated_data.densitometer_count > 0),
            '[]'::json) AS densitometer,
            COALESCE(
                JSON_AGG(
                    JSON_BUILD_OBJECT('id', aggregated_data.id, 'count', aggregated_data.ct_with_ku_1_zone_count)
                ) FILTER (WHERE aggregated_data.ct_with_ku_1_zone_count > 0),
            '[]'::json) AS ct_with_ku_1_zone,
            COALESCE(
                JSON_AGG(
                    JSON_BUILD_OBJECT('id', aggregated_data.id, 'count', aggregated_data.mri_with_ku_1_zone_count)
                ) FILTER (WHERE aggregated_data.mri_with_ku_1_zone_count > 0),
            '[]'::json) AS mri_with_ku_1_zone,
            COALESCE(
                JSON_AGG(
                    JSON_BUILD_OBJECT('id', aggregated_data.id, 'count', aggregated_data.ct_with_ku_more_than_1_zone_count)
                ) FILTER (WHERE aggregated_data.ct_with_ku_more_than_1_zone_count > 0),
            '[]'::json) AS ct_with_ku_more_than_1_zone,
            COALESCE(
                JSON_AGG(
                    JSON_BUILD_OBJECT('id', aggregated_data.id, 'count', aggregated_data.mri_with_ku_more_than_1_zone_count)
                ) FILTER (WHERE aggregated_data.mri_with_ku_more_than_1_zone_count > 0),
            '[]'::json) AS mri_with_ku_more_than_1_zone
        FROM (
            SELECT ws.year, ws.week_number, ws.is_new, ws.id,
                SUM(CASE WHEN c.code = '".StudyType::CT->value."' THEN ws.count ELSE 0 END) AS ct_count,
                SUM(CASE WHEN c.code = '".StudyType::MRI->value."' THEN ws.count ELSE 0 END) AS mri_count,
                SUM(CASE WHEN c.code = '".StudyType::RG->value."' THEN ws.count ELSE 0 END) AS rg_count,
                SUM(CASE WHEN c.code = '".StudyType::FLG->value."' THEN ws.count ELSE 0 END) AS flg_count,
                SUM(CASE WHEN c.code = '".StudyType::MMG->value."' THEN ws.count ELSE 0 END) AS mmg_count,
                SUM(CASE WHEN c.code = '".StudyType::DENSITOMETER->value."' THEN ws.count ELSE 0 END) AS densitometer_count,
                SUM(CASE WHEN c.code = '".StudyType::CT_WITH_KU_1_ZONE->value."' THEN ws.count ELSE 0 END) AS ct_with_ku_1_zone_count,
                SUM(CASE WHEN c.code = '".StudyType::MRI_WITH_KU_1_ZONE->value."' THEN ws.count ELSE 0 END) AS mri_with_ku_1_zone_count,
                SUM(CASE WHEN c.code = '".StudyType::CT_WITH_KU_MORE_THAN_1_ZONE->value."' THEN ws.count ELSE 0 END) AS ct_with_ku_more_than_1_zone_count,
                SUM(CASE WHEN c.code = '".StudyType::MRI_WITH_KU_MORE_THAN_1_ZONE->value."' THEN ws.count ELSE 0 END) AS mri_with_ku_more_than_1_zone_count
            FROM week_studies ws
            LEFT JOIN competencies c ON ws.competency_id = c.id
            GROUP BY ws.year, ws.week_number, ws.is_new, ws.id
        ) AS aggregated_data
        GROUP BY aggregated_data.year, aggregated_data.week_number, aggregated_data.is_new
        ORDER BY aggregated_data.year ASC, aggregated_data.week_number ASC
    ";

        $stmt = $conn->prepare($sql);

        return $stmt->executeQuery()->fetchAllAssociative();
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
