<?php

declare(strict_types=1);

namespace App\Modules\Algorithm;

use App\Entity\Competencies;
use App\Entity\Doctor;
use App\Entity\WeekStudies;
use Doctrine\ORM\EntityManagerInterface;

class AlgorithmWeekService
{
    private const EVOLUTION_COUNT = 20;
    private const POPULATION_COUNT = 10;

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
        set_time_limit(600);
        ini_set('memory_limit', '1024M');
        // Инициализация начальной популяции
        $population = $this->initializePopulation();

        // Цикл эволюции
        for ($i = 0; $i < self::EVOLUTION_COUNT; $i++) {
            $population = $this->evolvePopulation($population);
        }

        dd($population[0]);
    }

    // Метод для инициализации начальной популяции
    private function initializePopulation(): array
    {
        $population = [];
        for ($i = 0; $i < self::POPULATION_COUNT; $i++) {
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
            $modalityDayCount = (int) ($modalityWeekCount->getCount() / 7);
            $cyclePerDayCount = round($modalityDayCount / $modalityCompetency->getMinimalCountPerShift());
            for ($day = 1; $day <= 7; $day++) {
                $schedule[$modalityCompetency->getModality()][$day]['empty'] = 0;
                $schedule[$modalityCompetency->getModality()][$day]['ost'] = 0;
                $ostCount = $modalityDayCount;

                //TODO: Если у нас количество изначально меньше минимальной нормы,
                // то мы должны попытаться раскидать это на врачей, которым уже назначены другие исследования в этот день, чтобы не брать нового врача
                for ($i = 0; $i <= $cyclePerDayCount; $i++) {
                    //TODO: в первую очередь выбираем тех врачей, у кого цикл работы будет продолжен. Иначе берем нового
                    $doctor = $this->getRandomDoctorWhoCan($modalityCompetency, $schedule, $day);

                    if (!$doctor) {
                        $schedule[$modalityCompetency->getModality()][$day]['empty'] = $ostCount;
                        continue 2;
                    } elseif ($ostCount >= $modalityCompetency->getMinimalCountPerShift() && $ostCount <= $modalityCompetency->getMaxCountPerShift()) {
                        $schedule[$modalityCompetency->getModality()][$day][$doctor->getId()] = $ostCount;
                        continue 2;
                    } elseif ($ostCount >= $modalityCompetency->getMinimalCountPerShift()) {
                        $countPerShift = rand(
                            (int) round($modalityCompetency->getMinimalCountPerShift()),
                            (int) floor($modalityCompetency->getMaxCountPerShift())
                        );
                        $schedule[$modalityCompetency->getModality()][$day][$doctor->getId()] = $countPerShift;
                        $ostCount = $ostCount - $countPerShift;
                    } else {
                        //TODO: пытаемся распихать оставшиеся по уже назначенным врачам, чтобы не брать нового врача
                        $schedule[$modalityCompetency->getModality()][$day]['ost'] = $ostCount;
                        continue 2;
                    }
                }

                //TODO: После составления расписания на день, пытаемся уравновесить нагрузку по врачам
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
        $doctorsAlreadyIn = array_keys($doctorsCountStudies[$competency->getModality()][$day]);

        $doctorsWithCompetency = array_filter($this->doctors, function ($doctor) use ($competency, $doctorsAlreadyIn) {
            return in_array($competency->getModality(), $doctor->getCompetency()) && !in_array($doctor->getId(), $doctorsAlreadyIn);
        });

        if (empty($doctorsWithOkCoefficient)) {
            $doctorsWithCompetency = array_filter($this->doctors, function ($doctor) use ($competency, $doctorsAlreadyIn) {
                return in_array($competency->getModality(), $doctor->getAddonCompetencies()) && !in_array($doctor->getId(), $doctorsAlreadyIn);
            });
        }

        //TODO: Проверка на выходной
        //TODO: Проверка на месячную норму
        if (count($doctorsWithCompetency) > 0) {
            return $doctorsWithCompetency[array_rand($doctorsWithCompetency)];
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
        dd($newPopulation);

        // Мутация
        foreach ($newPopulation as &$individual) {
            if (rand(0, 100) < 10) {
                $individual = $this->mutate($individual);
            }
        }

        return $newPopulation;
    }

    // Метод оценки расписания
    private function calculateFitness(array $schedule): int
    {
        //TODO: Количество неназначенных исследований
        //TODO: Количество врачей, у которых переработка? Или выход в доп смену
        //TODO: Сравнение "баланса" нагрузки на врачей
        $fitness = 0;

        return $fitness;
    }

    // Метод для скрещивания (crossover)
    private function crossover(array $parent1, array $parent2): array
    {
// TODO 1 вариант скрещивания (просто соединение двух массивов по заданому срезу)

//        $child = [];
//        foreach ($this->modalities as $modality) {
//            $child[$modality->getModality()] = array_merge(
//                array_slice($parent1[$modality->getModality()], 0, 3),
//                array_slice($parent2[$modality->getModality()], 3)
//            );
//        }
//        return $child;


// TODO 2 вариант скрещивания (рандомный срез + инверсионное скрещивание + выбор лучшего результата с помощью фитнес функции)

        $child1 = [];
        $child2 = [];
        foreach ($this->modalities as $modality) {
            $cutPoint = rand(0, count($parent1[$modality->getModality()]) - 1);
            $child1[$modality->getModality()] = array_merge(
                array_slice($parent1[$modality->getModality()], 0, $cutPoint),
                array_slice($parent2[$modality->getModality()], $cutPoint)
            );
            $child2[$modality->getModality()] = array_merge(
                array_slice($parent2[$modality->getModality()], 0, $cutPoint),
                array_slice($parent1[$modality->getModality()], $cutPoint)
            );
        }
        return $this->calculateFitness($child1) < $this->calculateFitness($child2) ? $child1 : $child2;

// TODO 3 вариант скрещивания самый эффективный (предыдущий алгоритмы только в цикле с сравнением с худшим родителем)

//        do {
//            $child1 = [];
//            $child2 = [];
//            foreach ($this->modalities as $modality) {
//                $cutPoint = rand(0, count($parent1[$modality->getModality()]) - 1);
//                $child1[$modality->getModality()] = array_merge(
//                    array_slice($parent1[$modality->getModality()], 0, $cutPoint),
//                    array_slice($parent2[$modality->getModality()], $cutPoint)
//                );
//                $child2[$modality->getModality()] = array_merge(
//                    array_slice($parent2[$modality->getModality()], 0, $cutPoint),
//                    array_slice($parent1[$modality->getModality()], $cutPoint)
//                );
//            }
//
//            $parentFitness = min($this->calculateFitness($parent1), $this->calculateFitness($parent2));
//            $child1Fitness = $this->calculateFitness($child1);
//            $child2Fitness = $this->calculateFitness($child2);
//        } while ($child1Fitness >= $parentFitness && $child2Fitness >= $parentFitness);
//
//        return $child1Fitness < $child2Fitness ? $child1 : $child2;
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