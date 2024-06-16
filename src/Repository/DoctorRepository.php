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
                ->andWhere(sprintf('JSONB_CONTAINS(d.mainCompetencies, \'"%s"\') = true', quotemeta($modality)));
        }

        if ($addonModality) {
            $qb
                ->andWhere(sprintf('JSONB_CONTAINS(d.addonCompetencies, \'"%s"\') = true', quotemeta($addonModality)));
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
