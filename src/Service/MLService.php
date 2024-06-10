<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\WeekStudies;
use App\Enum\StudyType;
use App\Repository\WeekStudiesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class MLService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function execute(): void
    {
        $data = $this->getData();

        $command = $this->getCommand($data['data']);

        try {
            exec($command, $outputLines, $returnVar);
            if ($returnVar !== 0) {
                throw new \RuntimeException('Command failed with return code: ' . $returnVar . ' and output: ' . implode("\n", $outputLines));
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        if ($returnVar === 1) {
            $this->markData($data['ids']);
        } else {
            throw new \Exception('Ошибка обучения моделей: '. implode('. ', $outputLines));
        }
    }

    private function getData(): array
    {
        /** @var WeekStudiesRepository $repository */
        $repository = $this->em->getRepository(WeekStudies::class);

        $result = [];
        $ids = [];
        foreach ($repository->getStructuredData() as $structuredData) {
            $result[] = $this->prepareModelFromData($structuredData);
            if ($structuredData['is_new']) {
                $ids = array_merge($ids, $this->getIds($structuredData));
            }
        }

        return [
            'data' => $result,
            'ids' => $ids,
        ];
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

    private function getIds(array $data): array
    {
        $ids = [];
        foreach ($data as $item) {
            if (is_string($item) && ($info = json_decode($item, true))) {
                $ids[] = $info[0]['id'];
            }
        }

        return $ids;
    }

    private function markData(array $data): void
    {
        /** @var WeekStudiesRepository $repository */
        $repository = $this->em->getRepository(WeekStudies::class);
        $newData = $repository->findBy(['id' => $data]);

        /** @var WeekStudies $weekStudies */
        foreach ($newData as $weekStudies) {
            $weekStudies->setIsNotNew();
        }

        $this->em->flush();
    }

    private function getCommand(array $data): string
    {
        $dataJson = json_encode($data);
        $escapedDataJson = escapeshellarg($dataJson);

        $fileSystem = new Filesystem();
        $filePath = __DIR__.'/../script/train_model2.py';

        if ($fileSystem->exists($filePath)) {
            return "python3 $filePath $escapedDataJson";
        } else {
            dd("Ошибка $filePath");
        }

        return "python3 ./script/train_model.py $escapedDataJson";
    }
}
