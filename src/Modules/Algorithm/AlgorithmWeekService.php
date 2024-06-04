<?php

declare(strict_types=1);

namespace App\Modules\Algorithm;

use App\Entity\Competencies;
use App\Entity\Doctor;
use Doctrine\ORM\EntityManagerInterface;

class AlgorithmWeekService
{
    private array $doctors;
    private array $modalities;

    public function __construct(private EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->doctors = $entityManager->getRepository(Doctor::class)->findAll();
        $this->modalities = $entityManager->getRepository(Competencies::class)->findAll();
    }

    // Основной метод для генерации расписания
    public function run(): void
    {
        // Инициализация начальной популяции
        $population = $this->initializePopulation();

        // Цикл эволюции
        for ($i = 0; $i < 100; $i++) {
            $population = $this->evolvePopulation($population);
        }

        dd($population[0]);
    }

    // Метод для инициализации начальной популяции
    private function initializePopulation(): array
    {
        $population = [];
        for ($i = 0; $i < 50; $i++) {
            $population[] = $this->createRandomSchedule();
        }
        return $population;
    }

    // Метод для создания случайного расписания
    private function createRandomSchedule(): array
    {
        $schedule = [];
        foreach ($this->modalities as $modality) {
            $schedule[$modality->getName()] = [];
            for ($j = 0; $j < 7; $j++) {
                $doctor = $this->getRandomDoctorWithCompetency($modality->getName());
                if ($doctor) {
                    $schedule[$modality->getName()][] = $doctor->getId();
                } else {
                    // Если не найден врач с нужной компетенцией, назначаем null или можем обработать по-другому
                    $schedule[$modality->getName()][] = null; // или $this->doctors[array_rand($this->doctors)]->getId();
                }
            }
        }
        return $schedule;
    }

    // Метод для получения случайного врача с нужной компетенцией
    private function getRandomDoctorWithCompetency(string $modalityName): ?Doctor
    {
        $competentDoctors = array_filter($this->doctors, function ($doctor) use ($modalityName) {
            return in_array($modalityName, $doctor->getCompetencies());
        });

        if (count($competentDoctors) > 0) {
            return $competentDoctors[array_rand($competentDoctors)];
        } else {
            return null;
        }
    }

    // Метод для эволюции популяции
    private function evolvePopulation(array $population): array
    {
        // Селекция (отбор)
        usort($population, function ($a, $b) {
            return $this->calculateFitness($a) <=> $this->calculateFitness($b);
        });

        $newPopulation = [];
        for ($i = 0; $i < count($population) / 2; $i++) {
            $parent1 = $population[$i];
            $parent2 = $population[$i + 1];
            $newPopulation[] = $this->crossover($parent1, $parent2);
        }

        // Мутация
        foreach ($newPopulation as &$individual) {
            if (rand(0, 100) < 10) {
                $individual = $this->mutate($individual);
            }
        }

        return $newPopulation;
    }

    // Метод для вычисления приспособленности (fitness function)
    private function calculateFitness(array $schedule): int
    {
        $fitness = 0;

        foreach ($this->modalities as $modality) {
            $doctorCounts = array_count_values($schedule[$modality->getName()]);

            foreach ($doctorCounts as $doctorId => $count) {
                $doctor = $this->entityManager->getRepository(Doctor::class)->find($doctorId);

                if ($doctor && in_array($modality->getName(), $doctor->getCompetencies())) {
                    // Проверка минимального и максимального количества исследований за смену
                    if ($count >= $doctor->getMinStudiesPerShift() && $count <= $doctor->getMaxStudiesPerShift()) {
                        $fitness += $count;
                    } else {
                        // Уменьшение приспособленности, если количество исследований выходит за пределы допустимого
                        $fitness -= abs($count - $doctor->getMaxStudiesPerShift());
                    }
                }
            }
        }

        // Проверка на месячные лимиты
        foreach ($this->doctors as $doctor) {
            $totalStudiesPerMonth = 0;
            foreach ($this->modalities as $modality) {
                $totalStudiesPerMonth += array_count_values($schedule[$modality->getName()])[$doctor->getId()] ?? 0;
            }
            if ($totalStudiesPerMonth < $doctor->getMinStudiesPerMonth() || $totalStudiesPerMonth > $doctor->getMaxStudiesPerMonth()) {
                $fitness -= abs($totalStudiesPerMonth - $doctor->getMaxStudiesPerMonth());
            }
        }

        return $fitness;
    }

    // Метод для скрещивания (crossover)
    private function crossover(array $parent1, array $parent2): array
    {
        $child = [];
        foreach ($this->modalities as $modality) {
            $child[$modality->getName()] = array_merge(
                array_slice($parent1[$modality->getName()], 0, 3),
                array_slice($parent2[$modality->getName()], 3)
            );
        }
        return $child;
    }

    // Метод для мутации (mutation)
    private function mutate(array $individual): array
    {
        foreach ($this->modalities as $modality) {
            if (rand(0, 100) < 20) {
                $doctor = $this->getRandomDoctorWithCompetency($modality->getName());
                if ($doctor) {
                    $individual[$modality->getName()][rand(0, 6)] = $doctor->getId();
                }
            }
        }
        return $individual;
    }
}