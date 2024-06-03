<?php

declare(strict_types=1);

namespace App\Modules\Algorithm;

use App\Entity\Doctor;
use Doctrine\ORM\EntityManagerInterface;

class AlgorithmWeekService
{
    private float $maxCoefficientPerShift = 100.0; // Максимальная норма коэффициента за смену
    private int $populationSize = 100;
    private float $mutationRate = 0.01;
    private float $crossoverRate = 0.7;
    private int $generations = 100;
    private array $population = [];

    /** @var Doctor[] */
    private array $doctors;

    /** @var array */
    private array $weeklyStudies;

    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    private function initializeEnv(): void
    {
        $this->doctors = $this->entityManager->getRepository(Doctor::class)->findAll();
    }

    public function run(array $weeklyStudies): void
    {
        set_time_limit(600);
        ini_set('memory_limit', '1024M');
        $this->initializeEnv();
        $this->weeklyStudies = $this->generateWeeklyStudies($weeklyStudies);
        $this->initializePopulation();

        for ($generation = 0; $generation < $this->generations; $generation++) {
            $this->population = $this->evolve($this->population);
        }

        $solution = $this->getBestSolution();

        file_put_contents(
            __DIR__.'/mocks/result.json',
            json_encode($solution, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        );
    }

    private function generateWeeklyStudies(array $weeklyStudies): array
    {
        $generatedStudies = [];
        foreach ($weeklyStudies as $study) {
            $count = $study['count'];
            for ($i = 0; $i < $count; $i++) {
                $generatedStudies[] = $study;
            }
        }
        shuffle($generatedStudies); // Перемешиваем исследования для случайного распределения
        return $generatedStudies;
    }

    private function initializePopulation(): void
    {
        for ($i = 0; $i < $this->populationSize; $i++) {
            $this->population[] = $this->createRandomIndividual();
        }
    }

    private function evolve(array $population): array
    {
        $newPopulation = [];

        for ($i = 0; $i < $this->populationSize; $i++) {
            $parent1 = $this->selectParent($population);
            $parent2 = $this->selectParent($population);

            $offspring = $this->crossover($parent1, $parent2);

            $offspring = $this->mutate($offspring);

            $newPopulation[] = $offspring;
        }

        return $newPopulation;
    }

    private function selectParent(array $population): array
    {
        // Турнирный отбор
        $tournamentSize = 3;
        $best = null;

        for ($i = 0; $i < $tournamentSize; $i++) {
            $individual = $population[array_rand($population)];
            if ($best === null || $this->evaluateFitness($individual) > $this->evaluateFitness($best)) {
                $best = $individual;
            }
        }

        return $best;
    }

    private function crossover(array $parent1, array $parent2): array
    {
        if (rand(0, 100) / 100.0 < $this->crossoverRate) {
            $crossoverPoint = rand(0, count($parent1) - 1);
            $child = array_merge(array_slice($parent1, 0, $crossoverPoint), array_slice($parent2, $crossoverPoint));
            return $child;
        }

        return rand(0, 1) ? $parent1 : $parent2;
    }

    private function mutate(array $individual): array
    {
        for ($i = 0; $i < count($individual); $i++) {
            if (rand(0, 100) / 100.0 < $this->mutationRate) {
                $individual[$i] = $this->createRandomGene();
            }
        }

        return $individual;
    }

    private function createRandomIndividual(): array
    {
        $individual = [];

        foreach ($this->weeklyStudies as $study) {
            // Находим врачей, которые могут взяться за это исследование
            $availableDoctors = array_filter($this->doctors, function ($doctor) use ($study, $individual) {
                return $this->can($doctor, $study, $individual);
            });

            if (!empty($availableDoctors)) {
                $selectedDoctor = $availableDoctors[array_rand($availableDoctors)];
                $individual[] = ['study' => $study, 'doctor' => $selectedDoctor];
            } else {
                $individual[] = ['study' => $study, 'doctor' => null];
            }
        }

        return $individual;
    }

    private function createRandomGene(): array
    {
        $study = $this->weeklyStudies[array_rand($this->weeklyStudies)];

        $availableDoctors = array_filter($this->doctors, function ($doctor) use ($study) {
            return $this->can($doctor, $study, []);
        });

        if (!empty($availableDoctors)) {
            $selectedDoctor = $availableDoctors[array_rand($availableDoctors)];
            return ['study' => $study, 'doctor' => $selectedDoctor];
        } else {
            return ['study' => $study, 'doctor' => null];
        }
    }

    private function can(Doctor $doctor, array $study, array $individual): bool
    {
        // Проверка на компетенции врача
        if (!$doctor->getCompetencies()->contains($study['competency'])) {
            return false;
        }

        // Получаем уже назначенные исследования для данного врача на смену
        $studiesDuringShift = $this->getStudiesDuringShift($doctor, $study, $individual);

        // Проверка на пересечение исследований
        foreach ($studiesDuringShift as $assignedStudy) {
            if ($this->isOverlapping($assignedStudy, $study)) {
                return false;
            }
        }

        // Подсчет коэффициента нагрузки на врача
        $totalCoefficient = array_reduce($studiesDuringShift, function ($carry, $assignedStudy) {
            return $carry + $assignedStudy['competency']->getCoefficient();
        }, 0);

        // Проверка на превышение нормы нагрузки
        if ($totalCoefficient + $study['competency']->getCoefficient() > $this->maxCoefficientPerShift) {
            return false;
        }

        return true;
    }

    private function getStudiesDuringShift(Doctor $doctor, array $study, array $individual): array
    {
        $shiftStudies = [];

        foreach ($individual as $assignment) {
            if ($assignment['doctor'] === $doctor) {
                $assignedStudy = $assignment['study'];
                $assignedStudyDate = $assignedStudy['date'];

                // Предполагаем, что исследование назначено на смену, если его время начала совпадает с текущей сменой
                if ($assignedStudyDate->format('Y-m-d') === $study['date']->format('Y-m-d')) {
                    $shiftStudies[] = $assignedStudy;
                }
            }
        }

        return $shiftStudies;
    }

    private function isOverlapping(array $assignedStudy, array $newStudy): bool
    {
        $assignedStart = $assignedStudy['date'];
        $assignedEnd = $assignedStudy['end_time'];
        $newStart = $newStudy['date'];
        $newEnd = $newStudy['end_time'];

        return $assignedStart < $newEnd && $newStart < $assignedEnd;
    }

    private function evaluateFitness(array $individual): int
    {
        $fitness = 0;
        foreach ($individual as $gene) {
            if ($gene['doctor'] !== null) {
                $fitness += 1;
            }
        }

        return $fitness;
    }

    private function getBestSolution(): array
    {
        $bestIndividual = null;
        $bestFitness = 0;

        foreach ($this->population as $individual) {
            $fitness = $this->evaluateFitness($individual);
            if ($fitness > $bestFitness) {
                $bestFitness = $fitness;
                $bestIndividual = $individual;
            }
        }

        return $bestIndividual;
    }
}