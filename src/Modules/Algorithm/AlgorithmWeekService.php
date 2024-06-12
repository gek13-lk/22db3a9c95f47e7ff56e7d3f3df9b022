<?php

declare(strict_types=1);

namespace App\Modules\Algorithm;

use App\Entity\Competencies;
use App\Entity\Doctor;
use App\Entity\TempDoctorSchedule;
use App\Entity\TempSchedule;
use App\Entity\TempScheduleWeekStudies;
use App\Entity\WeekStudies;
use Doctrine\ORM\EntityManagerInterface;

class AlgorithmWeekService
{
    private array $doctorsStat = [];
    private const EVOLUTION_COUNT = 20;
    private const POPULATION_COUNT = 10;

    /** @var Competencies[]  */
    private array $modalities;
    private array $doctorsInSchedule = [];
    private string $currentDay;

    private array $weeksNumber;
    private array $doctors;
    private \DateTime $endDay;

    private const AVERAGE_SCORE = 100000;
    private const LOW_LEVEL = 'LOW_LEVEL';
    private const MIDDLE_LEVEL = 'MIDDLE_LEVEL';
    private const HIGH_LEVEL = 'HIGH_LEVEL';
    private const SUPER_HIGH_LEVEL = 'SUPER_HIGH_LEVEL';

    public function __construct(private EntityManagerInterface $entityManager)
    {
        $this->modalities = $this->entityManager->getRepository(Competencies::class)->findAll();

        $this->doctors = $this->entityManager->getRepository(Doctor::class)->findAll();
    }

    // Основной метод для генерации расписания
    public function run(\DateTime $startDay, \DateTime $endDay, int $countSchedule): void
    {
        $this->endDay = $endDay;
        $this->weeksNumber = $this->entityManager->getRepository(WeekStudies::class)->getAllWeekNumbers(
            $startDay, $endDay
        );
        set_time_limit(600);
        ini_set('memory_limit', '-1');
        // Инициализация начальной популяции
        $population = $this->initializePopulation();

        // Эволюция популяции
        $evolutionPopulation = $this->evolvePopulation(array_values($population), $countSchedule);

        $evolutionPopulation = array_map(
            fn($ep) => [
                'schedule' => $ep,
                'fitnessScore' => $this->calculateFitness($ep)
            ],
            $evolutionPopulation);

        usort($evolutionPopulation, function ($a, $b) {
            return ($a['fitnessScore'] - $b['fitnessScore']);
        });

        $bestPopulation = [];

        for ($i = 0; $i < $countSchedule; $i++) {
            $tempScheduleEntity = $this->saveTempSchedule($evolutionPopulation[$i]['schedule'], $evolutionPopulation[$i]['fitnessScore']);
            $bestPopulation[$tempScheduleEntity->getId()] = $evolutionPopulation[$i]['schedule'];
        }

        dd($bestPopulation);
    }

    // Метод для инициализации начальной популяции
    private function initializePopulation(): array
    {
        $population = [];
        for ($i = 1; $i <= self::POPULATION_COUNT; $i++) {
            $population[] = $this->createRandomSchedule();
        }

        return $population;
    }

    private function saveTempSchedule(array $randomSchedule, int $fitness): TempSchedule
    {
        $tempScheduleEntity = new TempSchedule();
        $tempScheduleEntity->setFitness($fitness);
        $this->entityManager->persist($tempScheduleEntity);

        foreach ($randomSchedule as $weekNumber => $scheduleForModality) {
            foreach ($scheduleForModality as $modality => $scheduleWeek) {
                $competency = $this->entityManager->getRepository(Competencies::class)->findOneBy([
                    'modality' => $modality
                ]);
                $weekStudies = $this->entityManager->getRepository(WeekStudies::class)->findOneBy([
                    'weekNumber' => $weekNumber,
                    'competency' => $competency
                ]);
                $tempScheduleWeekStudies = new TempScheduleWeekStudies();
                $tempScheduleWeekStudies->setTempSchedule($tempScheduleEntity);
                $tempScheduleWeekStudies->setWeekStudies($weekStudies);
                $empty = 0;
                foreach ($scheduleWeek as $day => $scheduleDay) {
                    foreach ($scheduleDay as $idDoctor => $stat) {
                        if ($idDoctor == 'empty') {
                            $empty = $stat;
                            continue;
                        }

                        $doctor = $this->entityManager->getRepository(Doctor::class)->find($idDoctor);
                        $tempDoctorSchedule = new TempDoctorSchedule();
                        $tempDoctorSchedule->setDoctor($doctor);
                        $tempDoctorSchedule->setDate(new \DateTime($day));
                        $tempDoctorSchedule->setTempSchedule($tempScheduleWeekStudies);
                        $this->entityManager->persist($tempDoctorSchedule);
                    }
                }

                $tempScheduleWeekStudies->setEmpty($empty);
                $this->entityManager->persist($tempScheduleWeekStudies);
            }
        }

        $this->entityManager->flush();

        return $tempScheduleEntity;
    }

    // Метод для создания случайного расписания
    private function createRandomSchedule(): array
    {
        $schedule = [];
        $this->doctorsStat = [];
        $this->doctorsInSchedule = [];
        foreach ($this->weeksNumber as $weekNumber) {
            //Перемешиваем модальности в неделе
            /** @var WeekStudies[] $weekStudies */
            $weekStudies = $this->entityManager->getRepository(WeekStudies::class)->findBy([
                'weekNumber' => $weekNumber['weekNumber'],
                'year' => $weekNumber['year'],
            ]);
            shuffle($weekStudies);

            $dayCount = 6;

            if (!empty($weekStudies)) {
                $diff = $weekStudies[0]->getStartOfWeek()->diff($this->endDay);

                if ($diff->days < 7) {
                    $dayCount = $diff->days;
                }
            }

            foreach ($weekStudies as $modalityWeek) {
                $modalityCompetency = $modalityWeek->getCompetency();
                $modalityDayCount = (int)($modalityWeek->getCount() / 7);
                $cyclePerDayCount = floor($modalityDayCount / $modalityCompetency->getMinimalCountPerShift());
                $ostPerDayCount = 0;

                for ($day = 0; $day <= $dayCount; $day++) {
                    $currentDate = (clone $modalityWeek->getStartOfWeek())->modify('+ ' . $day . ' days');
                    $currentDateString = $currentDate->format('Y-m-d');
                    $this->currentDay = $currentDateString;
                    //TODO: Можно остатки раскидывать по врачам, у которых есть компетенции в этом дне
                    $doctorsInDay = [];
                    $schedule[$modalityWeek->getWeekNumber()][$modalityCompetency->getModality()][$currentDateString]['empty'] = 0;
                    //Добавляем остаток с предыдущего дня (если есть)
                    $ostPerDayCount = $ostPerDayCount + $modalityDayCount;

                    //TODO: Если у нас количество исследований изначально меньше минимальной нормы,
                    // то мы должны попытаться раскидать это на врачей, которым уже назначены другие исследования в этот день, чтобы не брать нового врача
                    for ($i = 0; $i <= $cyclePerDayCount; $i++) {
                        $schedule[$modalityWeek->getWeekNumber()][$modalityCompetency->getModality()][$currentDateString]['empty'] = $ostPerDayCount;
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
                            $schedule[$modalityWeek->getWeekNumber()][$modalityCompetency->getModality()][$currentDateString][$doctor->getId()]['get'] = $ostPerDayCount;
                            $schedule[$modalityWeek->getWeekNumber()][$modalityCompetency->getModality()][$currentDateString][$doctor->getId()]['doMax'] = (int)$modalityDoctorMaxCountPerShift - $ostPerDayCount;
                            $doctorsInDay[] = $doctor->getId();
                            $this->doctorsStat[$doctor->getId()]['coefficient'] = $ostPerDayCount * $modalityCompetency->getCoefficient();
                            $this->doctorsStat[$doctor->getId()]['shiftCount']++;
                            $ostPerDayCount = 0;
                            $schedule[$modalityWeek->getWeekNumber()][$modalityCompetency->getModality()][$currentDateString]['empty'] = $ostPerDayCount;
                            $this->doctorsInSchedule[] = $doctor->getId();
                            continue 2;
                        } elseif ($ostPerDayCount >= $modalityDoctorMinimalCountPerShift) {
                            //TODO: Возможно Можно заменить на максимум?
                            $countPerShift = rand(
                                (int)round($modalityDoctorMinimalCountPerShift),
                                (int)floor($modalityDoctorMaxCountPerShift)
                            );
                            $schedule[$modalityWeek->getWeekNumber()][$modalityCompetency->getModality()][$currentDateString][$doctor->getId()]['get'] = $countPerShift;
                            $schedule[$modalityWeek->getWeekNumber()][$modalityCompetency->getModality()][$currentDateString][$doctor->getId()]['doMax'] = (int)$modalityDoctorMaxCountPerShift - $countPerShift;
                            $ostPerDayCount = $ostPerDayCount - $countPerShift;
                            $doctorsInDay[] = $doctor->getId();
                            $this->doctorsInSchedule[] = $doctor->getId();
                            $this->doctorsStat[$doctor->getId()]['coefficient'] = $countPerShift * $modalityCompetency->getCoefficient();
                            $this->doctorsStat[$doctor->getId()]['shiftCount']++;
                        } else {
                            $ostPerDayCount = $this->setOnActiveDoctors($schedule, $ostPerDayCount, $currentDateString, $modalityCompetency->getModality(), $modalityWeek->getWeekNumber());

                            //TODO: Возможно это делать только на последнем дне недели ?
                            if ($ostPerDayCount > 0) {
                                $schedule[$modalityWeek->getWeekNumber()][$modalityCompetency->getModality()][$currentDateString][$doctor->getId()]['get'] = $ostPerDayCount;
                                $schedule[$modalityWeek->getWeekNumber()][$modalityCompetency->getModality()][$currentDateString][$doctor->getId()]['doMax'] = (int)$modalityDoctorMaxCountPerShift - $ostPerDayCount;
                                $doctorsInDay[] = $doctor->getId();
                                $this->doctorsInSchedule[] = $doctor->getId();
                                $this->doctorsStat[$doctor->getId()]['coefficient'] = $ostPerDayCount * $modalityCompetency->getCoefficient();
                                $this->doctorsStat[$doctor->getId()]['shiftCount']++;
                                $ostPerDayCount = 0;
                            }

                            $schedule[$modalityWeek->getWeekNumber()][$modalityCompetency->getModality()][$currentDateString]['empty'] = $ostPerDayCount;
                            continue 2;
                        }
                    }

                    //TODO: После составления расписания на день, пытаемся уравновесить нагрузку по врачам
                }
            }
        }

        return $schedule;
    }

    // Пытаемся раскидать по врачам, которые уже в расписании
    private function setOnActiveDoctors(array $schedule, int $ostatok, string $currentDateString, string $modality, int $weekNumber): int
    {
        foreach ($schedule[$weekNumber][$modality][$currentDateString] as $key => $doctorDaySchedule) {
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

        if(!in_array($doctor->getId(), $this->doctorsStat)) {
            $this->doctorsStat[$doctor->getId()] = [
                'offCount' => 0,
                'shiftCount' => 0,
                'lastOffDay' => null,
            ];
        }

        // Инициализация
        if (!isset($this->doctorsStat[$doctor->getId()]['offCount']) || !isset($this->doctorsStat[$doctor->getId()]['shiftCount'])) {
            $this->doctorsStat[$doctor->getId()]['offCount'] = 0;
            $this->doctorsStat[$doctor->getId()]['shiftCount'] = 0;
            $this->doctorsStat[$doctor->getId()]['lastOffDay'] = null;
            return false;
        }

        if(!in_array('offCount', $this->doctorsStat[$doctor->getId()])) {
            $this->doctorsStat[$doctor->getId()]['offCount'] = 0;
        }

        if(!in_array('lastOffDay', $this->doctorsStat[$doctor->getId()])) {
            $this->doctorsStat[$doctor->getId()]['lastOffDay'] = 0;
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
            $doctorsIn = array_filter($this->doctors,
                fn(Doctor $doctor) => in_array($doctor->getId(), $this->doctorsInSchedule)
                    && !in_array($doctor->getId(), $doctorsAlreadyInDay)
                    && in_array($competency->getModality(), $doctor->getCompetency())
            );

            $doctors = array_filter($doctorsIn, function (Doctor $doctor) {
                return !$this->isDayOff($doctor);
            });
        }

        //Поиск врачей по основной компетенции
        if (empty($doctors)) {
            $doctors = array_filter($this->doctors,
                fn(Doctor $doctor) => !in_array($doctor->getId(), $doctorsAlreadyInDay)
                    && in_array($competency->getModality(), $doctor->getCompetency())
            );

            $doctors = array_filter($doctors, function (Doctor $doctor) {
                return !$this->isDayOff($doctor);
            });
        }

        //Пытаемся получить врачей по доп компетенции, которые подходят и которым получится продолжить цикл графика
        if (empty($doctors)) {
            $doctorsIn = array_filter($this->doctors,
                fn(Doctor $doctor) => in_array($doctor->getId(), $this->doctorsInSchedule)
                    && !in_array($doctor->getId(), $doctorsAlreadyInDay)
                    && in_array($competency->getModality(), $doctor->getAddonCompetencies())
            );

            $doctors = array_filter($doctorsIn, function (Doctor $doctor) {
                return !$this->isDayOff($doctor);
            });
        }

        //Поиск врачей по доп компетенции
        if (empty($doctors)) {
            $doctors = array_filter($this->doctors,
                fn(Doctor $doctor) => !in_array($doctor->getId(), $doctorsAlreadyInDay)
                    && in_array($competency->getModality(), $doctor->getAddonCompetencies())
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
    private function evolvePopulation(array $population, int $countSchedule): array
    {
        for ($i = 0; $i < self::EVOLUTION_COUNT && count($population)/2 >= $countSchedule; $i++) {
            $newPopulation = [];
            if ($this->calculateFitness($population[0]) < self::AVERAGE_SCORE) {
                return $population;
            }
            for ($i = 0; $i < count($population) / 2 && count($population) > 1; $i++) {
                $level = $this->getLevel($this->calculateFitness($population[$i]));
                $parent1 = $population[$i];
                $parent2 = $population[$i + 1];
                $newPopulation[] = $this->crossover($parent1, $parent2, $level);
            }

            // Мутация
            foreach ($newPopulation as &$individual) {
                $individual = $this->mutate($individual);
            }

            $population = $newPopulation;
        }
        return $population;
    }

    private function getLevel(int $score): string
    {
        if($score < self::AVERAGE_SCORE) {
            return self::SUPER_HIGH_LEVEL;
        } elseif($score < self::AVERAGE_SCORE * 2) {
            return self::HIGH_LEVEL;
        } elseif($score < self::AVERAGE_SCORE * 4) {
            return self::MIDDLE_LEVEL;
        }

        return self::LOW_LEVEL;
    }

    // Метод оценки расписания
    private function calculateFitness(array $schedule): int
    {
        //TODO: Количество неназначенных исследований
        //TODO: Количество врачей, у которых переработка? Или выход в доп смену
        //TODO: Сравнение "баланса" нагрузки на врачей
        $fitness = 0;
        $doctorWorkloads = [];
        $doctorDaysWorked = [];
        // Выбирается наилучшее расписание где меньше всего пропусков исследований/где больше назначеных врачей
        foreach ($schedule as $modality) {
            foreach ($modality as $week) {
                foreach ($week as $day) {
                    // TODO (1 проверка)  количество неназначенных исследований
                    if ($day['empty'] !== null) {
                        $fitness += $day['empty'] * 10; // штраф за неназначенное исследование (ввести очки)
                    }

                    // TODO Подсчет загрузки врачей и рабочих дней для выполнения следующих проверок
                    $doctors = array_filter(array_keys($day), fn($doctor) => $doctor !== 'empty');

                    foreach ($doctors as $doctor) {
                        if (!isset($doctorWorkloads[$doctor])) {
                            $doctorWorkloads[$doctor] = 0;
                        }
                        $doctorWorkloads[$doctor]++;

                        if (!isset($doctorDaysWorked[$doctor])) {
                            $doctorDaysWorked[$doctor] = [];
                        }
                        $randomDay = rand(0, 6); // случайный выбор дня для учета равномерного распределения
                        $doctorDaysWorked[$doctor][$randomDay] = true;
                    }
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

        // TODO (6 проверка) Минимизация выходных дней подряд
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

    // Метод для скрещивания
    private function crossover(array $parent1, array $parent2, string $populationLevel): array
    {
        return match ($populationLevel) {
            self::LOW_LEVEL => $this->highCrossing($parent1, $parent2),
            self::MIDDLE_LEVEL => $this->middleCrossing($parent1, $parent2),
            self::HIGH_LEVEL => $this->lowCrossing($parent1, $parent2),
            self::SUPER_HIGH_LEVEL => $this->calculateFitness($parent1) < $this->calculateFitness($parent2) ? $parent1 : $parent2,
        };
    }

    // Мутация
    private function mutate(array $individual): array
    {
        $oldIndividual = $individual;
        for ($i = 1; $i <= count($individual); $i++) {
            foreach ($this->modalities as $modality) {
                $rand = array_rand($individual[$i][$modality->getModality()]);
                $doctor = $this->getRandomDoctorWhoCan($modality, $individual[$i][$modality->getModality()][$rand]);
                if ($doctor) {
                    $doctorsId = array_filter(array_flip(array_keys($individual[$i][$modality->getModality()][$rand])), fn ($id) => $id !== 'empty');
                    if ($doctorsId) {
                        $randDoctorId = array_rand($doctorsId);
                        $individual[$i][$modality->getModality()][$rand][$randDoctorId] = $doctor->getId();
                    }
                }
            }
        }
        return $this->calculateFitness($individual) < $this->calculateFitness($oldIndividual) ? $individual : $oldIndividual;
    }

    /**
     * Вариант скрещивания для пополуляции высокого уровня
     * @param array $parent1
     * @param array $parent2
     * @return array
     */
    private function lowCrossing(array $parent1, array $parent2): array
    {
        $child = [];
        for ($i = 1; $i <= count($parent1); $i++) {
            foreach ($this->modalities as $modality) {
                $child[$i][$modality->getModality()] = array_merge(
                    array_slice($parent1[$i][$modality->getModality()], 0, 3),
                    array_slice($parent2[$i][$modality->getModality()], 3)
                );
            }
        }

        return $child;
    }

    /**
     * Вариант скрещивания для пополуляции среднего уровня
     * @param array $parent1
     * @param array $parent2
     * @return array
     */
    private function middleCrossing(array $parent1, array $parent2): array
    {
        $child1 = [];
        $child2 = [];
        for ($i = 1; $i <= count($parent1); $i++) {
            foreach ($this->modalities as $modality) {
                $cutPoint = rand(0, count($parent1[$i][$modality->getModality()]) - 1);
                $child1[$i][$modality->getModality()] = array_merge(
                    array_slice($parent1[$i][$modality->getModality()], 0, $cutPoint),
                    array_slice($parent2[$i][$modality->getModality()], $cutPoint)
                );
                $child2[$i][$modality->getModality()] = array_merge(
                    array_slice($parent2[$i][$modality->getModality()], 0, $cutPoint),
                    array_slice($parent1[$i][$modality->getModality()], $cutPoint)
                );
            }
        }
        return $this->calculateFitness($child1) < $this->calculateFitness($child2) ? $child1 : $child2;
    }

    /**
     * Вариант скрещивания для пополуляции низкого уровня
     * @param array $parent1
     * @param array $parent2
     * @return array
     */
    private function highCrossing(array $parent1, array $parent2): array
    {
        do {
            $child1 = [];
            $child2 = [];
            for ($i = 1; $i <= count($parent1); $i++) {
                foreach ($this->modalities as $modality) {
                    $cutPoint = rand(0, count($parent1[$i][$modality->getModality()]) - 1);
                    $child1[$i][$modality->getModality()] = array_merge(
                        array_slice($parent1[$i][$modality->getModality()], 0, $cutPoint),
                        array_slice($parent2[$i][$modality->getModality()], $cutPoint)
                    );
                    $child2[$i][$modality->getModality()] = array_merge(
                        array_slice($parent2[$i][$modality->getModality()], 0, $cutPoint),
                        array_slice($parent1[$i][$modality->getModality()], $cutPoint)
                    );
                }

                $parentFitness = min($this->calculateFitness($parent1), $this->calculateFitness($parent2));
                $child1Fitness = $this->calculateFitness($child1);
                $child2Fitness = $this->calculateFitness($child2);
            }
        } while ($child1Fitness >= $parentFitness && $child2Fitness >= $parentFitness);

        return $child1Fitness < $child2Fitness ? $child1 : $child2;
    }
}