<?php

declare(strict_types=1);

namespace App\Modules\Algorithm;

use App\Entity\Competencies;
use App\Entity\Doctor;
use Doctrine\ORM\EntityManagerInterface;

class AlgorithmService
{
    private int $populationSize;
    private float $mutationRate;
    private float $crossoverRate;
    private int $generations;
    private array $population = [];
    private array $doctors;
    private array $studies;

    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    public function run(): array
    {
        $this->initializeEnv();
        $this->initializePopulation();

        for ($generation = 0; $generation < $this->generations; $generation++) {
            $this->population = $this->evolve($this->population);
        }

        return $this->getBestSolution();
    }

    private function initializeEnv(): void
    {
        $this->doctors = $this->entityManager->getRepository(Doctor::class)->findAll();
        $studiesJson = file_get_contents(__DIR__.'/mocks/generatedData.json', true);
        $data = json_decode($studiesJson, true);
        $this->studies = $data['studies'];

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
        foreach ($this->studies as $study) {
            $availableDoctors = array_filter($this->doctors, function ($doctor) use ($study) {
                return in_array($study->competency, $doctor->competencies) && $this->hasAvailableTime($doctor, $study->duration);
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
        $study = $this->studies[array_rand($this->studies)];
        $availableDoctors = array_filter($this->doctors, function ($doctor) use ($study) {
            return in_array($study->competency, $doctor->competencies) && $this->hasAvailableTime($doctor, $study->duration);
        });
        if (!empty($availableDoctors)) {
            $selectedDoctor = $availableDoctors[array_rand($availableDoctors)];
            return ['study' => $study, 'doctor' => $selectedDoctor];
        } else {
            return ['study' => $study, 'doctor' => null];
        }
    }

    private function hasAvailableTime(Doctor $doctor, int $duration): bool
    {
        return array_sum($doctor->availableHours) >= $duration;
    }

    private function evaluateFitness(array $individual): int
    {
        $fitness = 0;
        foreach ($individual as $gene) {
            if ($gene['doctor'] !== null) {
                $fitness += 1; // Увеличиваем приспособленность за каждое корректное назначение
            }
        }
        return $fitness;
    }

    private function getBestSolution(): array
    {
        $bestIndividual = null;
        $bestFitness = -INF;

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