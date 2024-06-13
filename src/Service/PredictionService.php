<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Competencies;
use App\Entity\PredictedWeekStudies;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;

final class PredictionService
{
    private const URL = ':/python/prediction';

    public function __construct(
        private EntityManagerInterface $em,
        private RequestPython $python
    ) {
    }

    /**
     * @return array<PredictedWeekStudies>
     *
     * @throws \Exception
     * @throws GuzzleException
     */
    public function getPredictedDataByDate(\DateTime $date): array
    {
        $year = $date->format('Y');
        $month = $date->format('m');

        $response = $this->python->execute(self::URL, ['year' => $year, 'month' => $month]);
        if ($response) {
            return $this->updateData($response['result']);
        }

        return [];
    }

    private function updateData($output): array
    {
        $this->markData($output);

        $result = [];
        $competencies = $this->em->getRepository(Competencies::class)->findAll();
        foreach ($output as $data) {
            foreach ($competencies as $competency) {
                if (isset($data[$competency->getCode()->value])) {
                    $prediction = new PredictedWeekStudies();
                    $prediction->setCount($data[$competency->getCode()->value]);
                    $prediction->setCompetency($competency);
                    $prediction->setYear($data['Year']);
                    $prediction->setWeekNumber($data['Week']);

                    $this->em->persist($prediction);
                    $result[] = $prediction;
                }
            }
        }

        $this->em->flush();

        return $result;
    }

    private function markData($output): void
    {
        $years = array_column($output, 'Year');
        $weeks = array_column($output, 'Week');
        if (empty($years) || empty($weeks)) {
            return;
        }

        $predictions = $this->em->getRepository(PredictedWeekStudies::class)->findByYearAndWeeks(
            current($years),
            array_unique($weeks)
        );

        foreach ($predictions as $prediction) {
            $prediction->setIsNotNew();
        }
    }
}
