<?php

declare(strict_types=1);

namespace App\Modules\Algorithm;

use App\Entity\Competencies;
use App\Entity\Doctor;
use App\Entity\WeekStudies;
use Doctrine\ORM\EntityManagerInterface;

class AlgorithmWeekService
{
    private array $doctorsStat = [];
    private const EVOLUTION_COUNT = 20;
    private const POPULATION_COUNT = 10;

    /** @var Doctor[] */
    private array $doctors;


    /** @var WeekStudies[]  */
    private array $weekStudies;

    /** @var Competencies[]  */
    private array $modalities;

    private array $modalitiesWithCoefficient;
    private array $doctorsInSchedule = [];
    private string $currentDay;
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
        for ($i = 0; $i < self::EVOLUTION_COUNT && count($population) > 1; $i++) {
            $population = $this->evolvePopulation($population);
        }


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
        $this->doctorsStat = [];
        $this->doctorsInSchedule = [];
        foreach ($this->weekStudies as $modalityWeekCount) {
            $modalityCompetency = $modalityWeekCount->getCompetency();
            $modalityDayCount = (int) ($modalityWeekCount->getCount() / 7);
            $cyclePerDayCount = floor($modalityDayCount / $modalityCompetency->getMinimalCountPerShift());
            $ostPerDayCount = 0;
            for ($day = 0; $day <= 6; $day++) {
                $currentDate = (clone $modalityWeekCount->getStartOfWeek())->modify('+ '.$day.' days');
                $currentDateString = $currentDate->format('Y-m-d');
                $this->currentDay = $currentDateString;
                //TODO: Можно остатки раскидывать по врачам, у которых есть компетенции в этом дне
                $doctorsInDay = [];
                $schedule[$modalityCompetency->getModality()][$currentDateString]['empty'] = 0;
                //Добавляем остаток с предыдущего дня (если есть)
                $ostPerDayCount = $ostPerDayCount + $modalityDayCount;

                //TODO: Если у нас количество исследований изначально меньше минимальной нормы,
                // то мы должны попытаться раскидать это на врачей, которым уже назначены другие исследования в этот день, чтобы не брать нового врача
                for ($i = 0; $i <= $cyclePerDayCount; $i++) {
                    $schedule[$modalityCompetency->getModality()][$currentDateString]['empty'] = $ostPerDayCount;
                    $doctor = $this->getRandomDoctorWhoCan($modalityCompetency, $doctorsInDay);

                    if ($doctor) {
                        $modalityDoctorMinimalCountPerShift = round($modalityCompetency->getMinimalCountPerShift() * $doctor->getStavka());
                        $modalityDoctorMaxCountPerShift = floor($modalityCompetency->getMaxCountPerShift() * $doctor->getStavka());
                    }

                    if (!$doctor) {
                        //TODO: Попытаться распихать этот остаток в уже назначенных врачей на дню. Возможно у кого-то
                        // из других модальностей получится что-то взять
                        continue 2;
                    } elseif ($ostPerDayCount >= $modalityDoctorMinimalCountPerShift && $ostPerDayCount <= $modalityDoctorMaxCountPerShift) {
                        $schedule[$modalityCompetency->getModality()][$currentDateString][$doctor->getId()]['get'] = $ostPerDayCount;
                        $schedule[$modalityCompetency->getModality()][$currentDateString][$doctor->getId()]['doMax'] = (int) $modalityDoctorMaxCountPerShift - $ostPerDayCount;
                        $doctorsInDay[] = $doctor->getId();
                        $this->doctorsStat[$doctor->getId()]['coefficient'] = $ostPerDayCount * $modalityCompetency->getCoefficient();
                        $this->doctorsStat[$doctor->getId()]['shiftCount']++;
                        $ostPerDayCount = 0;
                        $schedule[$modalityCompetency->getModality()][$currentDateString]['empty'] = $ostPerDayCount;
                        $this->doctorsInSchedule[] = $doctor->getId();
                        continue 2;
                    } elseif ($ostPerDayCount >= $modalityDoctorMinimalCountPerShift) {
                        $countPerShift = rand(
                            (int) round($modalityDoctorMinimalCountPerShift),
                            (int) floor($modalityDoctorMaxCountPerShift)
                        );
                        $schedule[$modalityCompetency->getModality()][$currentDateString][$doctor->getId()]['get'] = $countPerShift;
                        $schedule[$modalityCompetency->getModality()][$currentDateString][$doctor->getId()]['doMax'] = (int) $modalityDoctorMaxCountPerShift - $countPerShift;
                        $ostPerDayCount = $ostPerDayCount - $countPerShift;
                        $doctorsInDay[] = $doctor->getId();
                        $this->doctorsInSchedule[] = $doctor->getId();
                        $this->doctorsStat[$doctor->getId()]['coefficient'] = $countPerShift * $modalityCompetency->getCoefficient();
                        $this->doctorsStat[$doctor->getId()]['shiftCount']++;
                    } else {
                        $ostPerDayCount = $this->setOnActiveDoctors($schedule, $ostPerDayCount, $currentDateString, $modalityCompetency->getModality());

                        if ($ostPerDayCount > 0) {
                            $schedule[$modalityCompetency->getModality()][$currentDateString][$doctor->getId()]['get'] = $ostPerDayCount;
                            $schedule[$modalityCompetency->getModality()][$currentDateString][$doctor->getId()]['doMax'] = (int) $modalityDoctorMaxCountPerShift - $ostPerDayCount;
                            $doctorsInDay[] = $doctor->getId();
                            $this->doctorsInSchedule[] = $doctor->getId();
                            $this->doctorsStat[$doctor->getId()]['coefficient'] = $ostPerDayCount * $modalityCompetency->getCoefficient();
                            $this->doctorsStat[$doctor->getId()]['shiftCount']++;
                            $ostPerDayCount = 0;
                        }

                        $schedule[$modalityCompetency->getModality()][$currentDateString]['empty'] = $ostPerDayCount;
                        continue 2;
                    }
                }

                //TODO: После составления расписания на день, пытаемся уравновесить нагрузку по врачам
            }
        }

        return $schedule;
    }

    // Пытаемся раскидать по врачам, которые уже в расписании
    private function setOnActiveDoctors(array $schedule, int $ostatok, string $currentDateString, string $modality): int
    {
        foreach ($schedule[$modality][$currentDateString] as $key => $doctorDaySchedule) {
            if ($key === 'empty') {
                continue;
            }

            if (!$doctorDaySchedule['doMax']) {
                continue;
            }

            if ($doctorDaySchedule['doMax'] >= $ostatok) {
                $doctorDaySchedule['get'] = $doctorDaySchedule['get'] + $ostatok;
                $doctorDaySchedule['doMax'] = $doctorDaySchedule['doMax'] - $ostatok;
                $doctorDaySchedule['ooo'] = 'ooo';
                $ostatok = 0;
                break;
            } else {
                $ostatok = $ostatok - $doctorDaySchedule['doMax'];
                $doctorDaySchedule['doMax'] = 0;
                $doctorDaySchedule['get'] = $doctorDaySchedule['get'] + $doctorDaySchedule['doMax'];
                $doctorDaySchedule['kkk'] = 'kkk';
            }
        }

        return $ostatok;
    }

    private function isDayOff(Doctor $doctor): bool
    {
        $doctorWorkSchedule = $doctor->getWorkSchedule();

        //TODO: Мб добавить здесь добавление какого-то по дефолту??
        if (!$doctorWorkSchedule) {
            return true;
        }

        // Инициализация
        if (!isset($this->doctorsStat[$doctor->getId()]['offCount']) || !isset($this->doctorsStat[$doctor->getId()]['shiftCount'])) {
            $this->doctorsStat[$doctor->getId()]['offCount'] = 0;
            $this->doctorsStat[$doctor->getId()]['shiftCount'] = 0;
            $this->doctorsStat[$doctor->getId()]['lastOffDay'] = null;
            return false;
        }

        // Выходные закончились
        if ($this->doctorsStat[$doctor->getId()]['offCount'] >= $doctorWorkSchedule->getDaysOff() &&
            $this->doctorsStat[$doctor->getId()]['lastOffDay'] != $this->currentDay) {
            $this->doctorsStat[$doctor->getId()]['offCount'] = 0;
            $this->doctorsStat[$doctor->getId()]['shiftCount'] = 0;

            return false;
        }

        // Выходной
        if ($this->doctorsStat[$doctor->getId()]['shiftCount'] >= $doctorWorkSchedule->getShiftPerCycle()) {
            if ($this->doctorsStat[$doctor->getId()]['lastOffDay'] != $this->currentDay) {
                $this->doctorsStat[$doctor->getId()]['offCount']++;
                $this->doctorsStat[$doctor->getId()]['lastOffDay'] = $this->currentDay;
            }

            return true;
        }

        return false;
    }

    // Метод для получения случайного врача с нужной компетенцией
    private function getRandomDoctorWhoCan(
        Competencies $competency,
        array $doctorsAlreadyInDay
    ): ?Doctor {
        $doctors = [];

        //Пытаемся получить врачей, которые подходят и которым получится продолжить цикл графика
        // (стараемся не вводить новых врачей в расписание)
        if (!empty($this->doctorsInSchedule)) {
            $doctorsIn = $this->entityManager->getRepository(Doctor::class)->findByIds(
                $this->doctorsInSchedule,
                $doctorsAlreadyInDay,
                $competency->getModality()
            );

            $doctors = array_filter($doctorsIn, function (Doctor $doctor) {
                return !$this->isDayOff($doctor);
            });
        }

        //Поиск врачей по основной компетенции
        if (empty($doctors)) {
            $doctors = $this->entityManager->getRepository(Doctor::class)->findByIds(
                exclude: $doctorsAlreadyInDay,
                modality: $competency->getModality()
            );

            $doctors = array_filter($doctors, function (Doctor $doctor) {
                return !$this->isDayOff($doctor);
            });
        }

        //Пытаемся получить врачей по доп компетенции, которые подходят и которым получится продолжить цикл графика
        if (empty($doctors)) {
            $doctorsIn = $this->entityManager->getRepository(Doctor::class)->findByIds(
                $this->doctorsInSchedule,
                $doctorsAlreadyInDay,
                addonModality: $competency->getModality()
            );

            $doctors = array_filter($doctorsIn, function (Doctor $doctor) {
                return !$this->isDayOff($doctor);
            });
        }

        //Поиск врачей по доп компетенции
        if (empty($doctors)) {
            $doctors = $this->entityManager->getRepository(Doctor::class)->findByIds(
                exclude: $doctorsAlreadyInDay,
                addonModality: $competency->getModality()
            );

            $doctors = array_filter($doctors, function (Doctor $doctor) {
                return !$this->isDayOff($doctor);
            });
        }

        //TODO: Проверка на месячную норму
        if (count($doctors) > 0) {
            return $doctors[array_rand($doctors)];
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
        for ($i = 0; $i < count($population) / 2 && count($population) > 1; $i++) {
            $parent1 = $population[$i];
            $parent2 = $population[$i + 1];
            $newPopulation[] = $this->crossover($parent1, $parent2);
        }

        // Мутация
        foreach ($newPopulation as &$individual) {
            $individual = $this->mutate($individual);
        }

        return $newPopulation;
    }

    // Метод оценки расписания
    private function calculateFitness(array $schedule): int
    {
        dump($schedule);
        //TODO: Количество неназначенных исследований
        //TODO: Количество врачей, у которых переработка? Или выход в доп смену
        //TODO: Сравнение "баланса" нагрузки на врачей
        $fitness = 0;
        $doctorWorkloads = [];
        $doctorDaysWorked = [];

        // Выбирается наилучшее расписание где меньше всего пропусков исследований/где больше назначеных врачей
        foreach ($schedule as $modality) {
            foreach ($modality as $week) {
                // TODO (1 проверка)  количество неназначенных исследований
                if ($week['empty'] !== null) {
                    $fitness += $week['empty'] * 10; // штраф за неназначенное исследование (ввести очки)
                }

                // TODO Подсчет загрузки врачей и рабочих дней для выполнения следующих проверок
                $doctors = array_filter(array_keys($week), fn ($doctor) => $doctor !== 'empty');

                foreach ($doctors as $doctor) {
                    if (!isset($doctorWorkloads[$doctor])) {
                        $doctorWorkloads[$doctor] = 0;
                    }
                    $doctorWorkloads[$doctor]++;

                    if (!isset($doctorDaysWorked[$doctor])) {
                        $doctorDaysWorked[$doctor] = [];
                    }
                    $day = rand(0, 6); // случайный выбор дня для учета равномерного распределения
                    $doctorDaysWorked[$doctor][$day] = true;
                }
                }
        }

        // TODO Подсчет загрузки врачей и рабочих дней: Мы считаем количество назначений для каждого врача в течение недели и отмечаем, в какие дни они работали.
        // TODO (2 проверка) Расчет очков на основе равномерности распределения врачей по дням: Если врач не работал в течение недели, то добавляется штраф.
        foreach ($doctorDaysWorked as $daysWorked) {
            if (count($daysWorked) < 5) {
                $fitness += (5 - count($daysWorked)) * 10;
            }
        }

        // TODO Дополнительные эвристики
        // TODO (3 проверка) Учет перегрузки врачей
        foreach ($doctorWorkloads as $workload) {
            if ($workload > 5) { // если количество назначений в неделю больше 5, добавляем штраф
                $fitness += ($workload - 5) * 20; // Штраф за перегрузку врача
            }
        }

        // TODO (4 проверка) Минимизация перерывов
        foreach ($doctorDaysWorked as $daysWorked) {
            $days = array_keys($daysWorked);
            for ($i = 1; $i < count($days); $i++) {
                if ($days[$i] - $days[$i - 1] > 1) {
                    $fitness += 5; // Штраф за большой перерыв между рабочими днями
                }
            }
        }

        // TODO (5 проверка) Равномерное распределение задач
        $avgWorkload = array_sum($doctorWorkloads) / count($doctorWorkloads);
        foreach ($doctorWorkloads as $workload) {
            if (abs($workload - $avgWorkload) > 2) {
                $fitness += 10; // Штраф за отклонение от средней нагрузки
            }
        }

        // TODO (56 проверка) Минимизация выходных дней подряд
        foreach ($doctorDaysWorked as $daysWorked) {
            $days = array_keys($daysWorked);
            $maxConsecutiveDaysOff = 0;
            $currentConsecutiveDaysOff = 0;

            for ($day = 0; $day < 7; $day++) {
                if (!isset($daysWorked[$day])) {
                    $currentConsecutiveDaysOff++;
                } else {
                    if ($currentConsecutiveDaysOff > $maxConsecutiveDaysOff) {
                        $maxConsecutiveDaysOff = $currentConsecutiveDaysOff;
                    }
                    $currentConsecutiveDaysOff = 0;
                }
            }
            if ($currentConsecutiveDaysOff > $maxConsecutiveDaysOff) {
                $maxConsecutiveDaysOff = $currentConsecutiveDaysOff;
            }

            if ($maxConsecutiveDaysOff > 2) {
                $fitness += ($maxConsecutiveDaysOff - 2) * 5; // Штраф за большое количество выходных дней подряд
            }
        }

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
// TODO: 1 варинат (как было)

//        foreach ($this->modalities as $modality) {
//            if (rand(0, 100) < 20) {
//                $doctor = $this->getRandomDoctorWhoCan($modality->getName());
//                if ($doctor) {
//                    $individual[$modality->getName()][rand(0, 6)] = $doctor->getId();
//                }
//            }
//        }
//        return $individual;

// TODO: поправил первый вариант (чтобы работало)

//        foreach ($this->modalities as $modality) {
//            if (rand(0, 100) < 20) {
//                $day = rand(0, 6);
//                $doctor = $this->getRandomDoctorWhoCan($modality->getModality(), $individual, $day);
//                if ($doctor) {
//                    $doctorsId = array_slice($individual[$modality->getModality()][$day], 2);
//                    if ($doctorsId) {
//                        $randDoctorId = array_rand($doctorsId);
//                        $individual[$modality->getModality()][$day][$randDoctorId] = $doctor->getId();
//                    }
//                }
//            }
//        }
//        return $individual;

// TODO: 2 варинат (сделал мутацию сто процентой по каждой модальности + мутация применяется только если фитнес уменьшилось)

        $oldIndividual = $individual;
        foreach ($this->modalities as $modality) {
                $day = rand(0, 6);
                $doctor = $this->getRandomDoctorWhoCan($modality->getModality(), $individual, $day);
                if ($doctor) {
                    $doctorsId = array_slice($individual[$modality->getModality()][$day], 1);
                    if ($doctorsId) {
                        $randDoctorId = array_rand($doctorsId);
                        $individual[$modality->getModality()][$day][$randDoctorId] = $doctor->getId();
                    }
                }
        }
        return $this->calculateFitness($individual) < $this->calculateFitness($oldIndividual) ? $individual : $oldIndividual;
    }
}