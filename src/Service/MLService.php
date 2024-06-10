<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\WeekStudies;
use App\Enum\StudyType;
use App\Repository\WeekStudiesRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MLService
{
    private Client $client;

    public function __construct(private EntityManagerInterface $em)
    {
        $this->client = new Client([
            'base_uri' => 'http://django:8000',
        ]);
    }

    public function execute(): void
    {
        $data = $this->getData();

        try {
            $response = $this->client->post('/python/train', [
                'json' => ['data' => $data['data']]
            ]);
dd($response);
            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            $output = json_decode($body->getContents(), true);

            if ($statusCode !== 200) {
                throw new \RuntimeException('Command failed with status code: ' . $statusCode . ' and output: ' . json_encode($output));
            }
        } catch (RequestException $e) {
            dd($e->getMessage());
        }
dd($outputLines, $returnVar);
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

    public function getPredictedData(\DateTime $date) {
        $year = $date->format('Y');
        $month = $date->format('m');

        try {
            $response = $this->client->post('/python/predicted_studies', [
                'json' => ['year' => $year, 'month' => $month]
            ]);
            dd($response);
            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            $output = json_decode($body->getContents(), true);

            if ($statusCode !== 200) {
                throw new \RuntimeException('Command failed with status code: ' . $statusCode . ' and output: ' . json_encode($output));
            }

            return $output;
        } catch (RequestException $e) {
            dd($e->getMessage());
        }
    }
}
