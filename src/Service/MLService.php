<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\WeekStudies;
use App\Enum\StudyType;
use App\Repository\WeekStudiesRepository;
use Doctrine\ORM\EntityManagerInterface;

class MLService
{
    private const URL = '/:/python/train';

    public function __construct(
        private EntityManagerInterface $em,
        private RequestPython $python
    ) {
    }

    public function execute(): void
    {
        $this->python->execute(self::URL, ['data' => $this->getData()]);
    }

    private function getData(): array
    {
        /** @var WeekStudiesRepository $repository */
        $repository = $this->em->getRepository(WeekStudies::class);

        $result = [];
        foreach ($repository->getStructuredData() as $structuredData) {
            $result[] = $this->prepareModelFromData($structuredData);
        }

        return $result;
    }

    private function prepareModelFromData(array $data): array
    {
        $model = [
            'Year' => $data['year'],
            'Week' => $data['week_number'],
        ];

        foreach (StudyType::cases() as $studyType) {
            $model['count_'.$studyType->value] = $this->getStudiesCount($data[$studyType->value]);
        }

        return $model;
    }

    private function getStudiesCount(string $json): int
    {
        $count = json_decode($json, true);

        return $count ? $count[0]['count'] : 0;
    }
}
