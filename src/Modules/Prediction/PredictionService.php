<?php

declare(strict_types=1);

namespace App\Modules\Prediction;

use DateTimeImmutable;

class PredictionService
{
    public function getPredictionsByDate(string $date): array
    {
        $date = new DateTimeImmutable($date);

        $command = sprintf(
            'python3 ../scripts/get_prediction.py %d %d',
            $date->format('Y'),
            $date->format('n')
        );

        exec($command, $output, $exitCode);

        $output = implode("\n", $output);

        if ($exitCode === 0) {
            throw new \Exception($output);
        }

        return json_decode($output, true);
    }
}

