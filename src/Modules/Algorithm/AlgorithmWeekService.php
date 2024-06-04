<?php

declare(strict_types=1);

namespace App\Modules\Algorithm;

use App\Entity\Competencies;
use App\Entity\Doctor;
use App\Entity\Studies;
use App\Entity\WeekStudies;
use Doctrine\ORM\EntityManagerInterface;

class AlgorithmWeekService
{
    /** @var Doctor[] */
    private array $doctors;


    /** @var WeekStudies[]  */
    private array $weekStudies;

    /** @var Competencies[]  */
    private array $modalities;

    private array $modalitiesWithCoefficient;

    public function __construct(private EntityManagerInterface $entityManager)
    {
        $this->doctors = $entityManager->getRepository(Doctor::class)->findAll();
        $this->weekStudies = $entityManager->getRepository(WeekStudies::class)->findAll();
        $this->modalities = $this->entityManager->getRepository(Competencies::class)->findAll();

        foreach ($this->modalities as $modality) {
            $this->modalitiesWithCoefficient[$modality->getModality()] = $modality->getCoefficient();
        }
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
        foreach ($this->weekStudies as $modalityWeekCount) {
            $modalityCompetency = $modalityWeekCount->getCompetency();
            $modalityDayCount = $modalityWeekCount->getCount() / 7;
            for ($day = 1; $day <= 7; $day++) {
                $doctorsCountStudies = [];
                //TODO: Подумать как облегчить
                for ($studyNumber = 1; $studyNumber <= $modalityDayCount; $studyNumber++) {
                    $schedule[$modalityCompetency->getModality()][$day] = [];
                    $doctor = $this->getRandomDoctorWhoCan($modalityCompetency, $doctorsCountStudies, $day);

                    if ($doctor) {
                        $schedule[$modalityCompetency->getModality()][$day][$doctor->getId()]++;
                        $doctorsCountStudies[$doctor->getId()][$day][$modalityCompetency->getModality()]++;
                    } else {
                        // Если не найден врач с нужной компетенцией, назначаем null или можем обработать по-другому
                        $schedule[$modalityCompetency->getModality()][$day]['empty']++; // или $this->doctors[array_rand($this->doctors)]->getId();
                    }
                }
            }
        }

        return $schedule;
    }

    private function getCoefficientForDay(array $doctorsCountStudiesPerDay): float
    {
        $result = 0;

        foreach ($doctorsCountStudiesPerDay as $key => $doctorModalityStudiesCount) {
            $result += $this->modalitiesWithCoefficient[$key] * $doctorModalityStudiesCount;
        }

        return $result;
    }

    // Метод для получения случайного врача с нужной компетенцией
    private function getRandomDoctorWhoCan(Competencies $competency, array $doctorsCountStudies, int $day): ?Doctor
    {
        $doctorsWithCompetency = array_filter($this->doctors, function ($doctor) use ($competency) {
            return in_array($competency->getModality(), $doctor->getCompetency());
        });

        $doctorsWithOkCoefficient = array_filter($doctorsWithCompetency, function ($doctor) use ($day, $doctorsCountStudies, $competency) {
            return $competency->getMaxCoefficientPerShift() >= $this->getCoefficientForDay($doctorsCountStudies[$doctor->getId()][$day]);
        });

        if (empty($doctorsWithOkCoefficient)) {
            $doctorsWithCompetency = array_filter($this->doctors, function ($doctor) use ($competency) {
                return in_array($competency->getModality(), $doctor->getAddonCompetencies());
            });

            $doctorsWithOkCoefficient = array_filter($doctorsWithCompetency, function ($doctor) use ($day, $doctorsCountStudies, $competency) {
                return $competency->getMaxCoefficientPerShift() >= $this->getCoefficientForDay($doctorsCountStudies[$doctor->getId()][$day]);
            });
        }

        if (count($doctorsWithOkCoefficient) > 0) {
            return $doctorsWithOkCoefficient[array_rand($doctorsWithOkCoefficient)];
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
                $doctor = $this->getRandomDoctorWhoCan($modality->getName());
                if ($doctor) {
                    $individual[$modality->getName()][rand(0, 6)] = $doctor->getId();
                }
            }
        }
        return $individual;
    }
}