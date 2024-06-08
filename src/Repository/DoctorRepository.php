<?php

namespace App\Repository;

use App\Entity\Doctor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Doctor::class);
    }

    /**
     * @return Doctor[]
     */
    public function findByIds(
        array $ids = [],
        array $exclude = [],
        ?string $modality = null,
        ?string $addonModality = null): array
    {
        $qb =
            $this
            ->createQueryBuilder('d');

        if (!empty($ids)) {
            $qb
                ->andWhere('d.id IN (:arrayIds)')
                ->setParameter('arrayIds', $ids);
        }

        if (!empty($exclude)) {
            $qb
                ->andWhere('d.id NOT IN (:arrayIdsExclude)')
                ->setParameter('arrayIdsExclude', $exclude);
        }

        if ($modality) {
            $qb
                ->andWhere(':competency IN (d.mainCompetencies)')
                ->setParameter('competency', $modality);
        }

        if ($addonModality) {
            $qb
                ->andWhere(':addonCompetency IN (d.addonCompetencies)')
                ->setParameter('addonCompetency', $addonModality);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
