<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Competencies;
use App\Entity\Doctor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class CompetenciesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competencies::class);
    }

    public function findOneByTypeOrModality(?string $modality = null, ?string $type = null): ?Competencies
    {
        if ($type) {
            return $this->createQueryBuilder('c')
                ->where('c.type = :type')
                ->setParameter('type', $type)
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();
        }

        if ($modality) {
            return $this->createQueryBuilder('c')
                ->where('c.modality = :modality')
                ->setParameter('modality', $modality)
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();
        }

        return null;
    }

    public function findByDoctor(Doctor $doctor, string $typeOrModality): ?Competencies
    {
        return $this->createQueryBuilder('c')
            ->join(Doctor::class, 'd', Join::WITH, 'd.id = :doctor')
            ->where('c.modality = :typeOrModality OR c.type = :typeOrModality')
            ->setParameter('typeOrModality', $typeOrModality)
            ->setParameter('doctor', $doctor)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}
