<?php

declare(strict_types=1);

namespace App\Modules\Algorithm;

use App\Entity\Doctor;
use App\Entity\DoctorWorkSchedule;
use App\Entity\Studies;
use Doctrine\ORM\EntityManagerInterface;

class AlgorithmService
{
    //TODO: Перенести в БД
    private float $maxCoefficientPerShift = 100; //Максимальная норма коэффициента за смену
    private int $populationSize = 35;
    private float $mutationRate = 0.01;
    private float $crossoverRate = 0.7;
    private int $generations = 50;
    private array $population = [];

    /** @var Doctor[] */
    private array $doctors;

    /** @var Studies[]  */
    private array $studies;

    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    private function initializeEnv(): void
    {
        $this->doctors = $this->entityManager->getRepository(Doctor::class)->findAll();
        $this->studies = $this->entityManager->getRepository(Studies::class)->findBy([], ['date' => 'ASC']);

    }

    public function run(): void
    {
        set_time_limit(600);
        ini_set('memory_limit', '1024M');

        $this->initializeEnv();
        $this->initializePopulation();

        for ($generation = 0; $generation < $this->generations; $generation++) {
            $this->population = $this->evolve($this->population);
        }

        $solution = $this->getBestSolution();
        //TODO: Анализ результата, предложения по возможным улучшениям
        file_put_contents(
            __DIR__.'/mocks/result.json',
            json_encode($solution, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        );
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
                $individual[$i] = $this->createRandomGene($individual);
            }
        }

        return $individual;
    }

    private function createRandomIndividual(): array
    {
        $individual = [];

        foreach ($this->studies as $study) {
            //Находим врачей, которые могут взяться за это исследование
            $availableDoctors = array_filter($study->getCompetency()->getDoctors()->toArray(), function ($doctor) use ($study, $individual) {
                return $this->can($doctor, $study, $individual);
            });

            if (!empty($availableDoctors)) {
                $selectedDoctor = $availableDoctors[array_rand($availableDoctors)];
                $individual[] = ['study' => $study, 'doctor' => $selectedDoctor];
            } else {
                $individual[] = ['study' => $study, 'doctor' => null];
            }
        }

        //Возвращаем пример составления расписания
        return $individual;
    }

    private function createRandomGene(array $individual): array
    {
        $study = $this->studies[array_rand($this->studies)];

        $availableDoctors = array_filter($study->getCompetency()->getDoctors()->toArray(), function ($doctor) use ($study, $individual) {
            return $this->can($doctor, $study, $individual);
        });

        if (!empty($availableDoctors)) {
            $selectedDoctor = $availableDoctors[array_rand($availableDoctors)];
            return ['study' => $study, 'doctor' => $selectedDoctor];
        } else {
            return ['study' => $study, 'doctor' => null];
        }
    }

    private function isAvailableDuringShift(DoctorWorkSchedule $workSchedule, Studies $study): bool
    {
        $shiftType = $workSchedule->getType();
        $studyStartTime = $study->getDate();
        $studyEndTime = $study->getEndTime();

        switch ($shiftType) {
            case DoctorWorkSchedule::TYPE_DAY:
                // Дневная смена: с 6:00 до 22:00
                $shiftStartTime = (clone $studyStartTime)->setTime(6, 0);
                $shiftEndTime = (clone $studyStartTime)->setTime(22, 0);
                break;
            case DoctorWorkSchedule::TYPE_NIGHT:
                // Ночная смена: с 22:00 до 6:00 следующего дня
                $shiftStartTime = (clone $studyStartTime)->setTime(22, 0);
                $shiftEndTime = (clone $studyStartTime)->modify('+1 day')->setTime(6, 0);
                break;
            case DoctorWorkSchedule::TYPE_ONE_TO_THREE:
                // Сутки через трое: 24 часа работы, затем 72 часа выходных
                $shiftStartTime = clone $studyStartTime;
                $shiftEndTime = (clone $studyStartTime)->modify('+24 hours');
                break;
            case DoctorWorkSchedule::TYPE_DAY_NIGHT:
                // День-ночь: предполагаем чередование дневных и ночных смен (здесь можно реализовать свою логику)
                // Для упрощения допускаем все времена
                return true;
            case DoctorWorkSchedule::TYPE_TWO_OFF:
                // Два выходных: после полного цикла смен два выходных дня (здесь можно реализовать свою логику)
                // Для упрощения допускаем все времена
                return true;
            default:
                return false;
        }

        // Проверяем, попадает ли время исследования в рабочее время врача
        return ($studyStartTime >= $shiftStartTime && $studyEndTime <= $shiftEndTime);
    }

    /**
     * Проверяет, пересекаются ли два исследования по времени.
     */
    private function studiesOverlap(\DateTime $start1, \DateTime $end1, \DateTime $start2, \DateTime $end2): bool
    {
        return ($start1 < $end2 && $start2 < $end1);
    }

    //Проверка возможности врача провести исследование с соблюдением норм
    private function can(Doctor $doctor, Studies $study, array $individual): bool
    {
        $doctorWorkSchedule = $doctor->getWorkSchedule();

        // Проверяем, подходит ли день для смены врача согласно его графику
        $dayOfWeek = (clone $study->getDate())->format('N'); // День недели (1-7, где 1 - понедельник)
        $dayInCycle = ($dayOfWeek - 1) % ($doctorWorkSchedule->getShiftPerCycle() + $doctorWorkSchedule->getDaysOff());

        if ($dayInCycle >= $doctorWorkSchedule->getShiftPerCycle()) {
            return false; // Врач на выходном
        }

        if (!$this->isAvailableDuringShift($doctorWorkSchedule, $study)) {
            return false;
        }

        //$durationSum = null;
        $coefficient = null;

        //Смотрим, чтобы время работы от самого раннего приема до самого позднего не выходило за рамки рабочего дня
        foreach ($individual as $studyIndividual) {
            if ($studyIndividual['doctor'] === $doctor) {
                /** @var Studies $doctorStudy */
                $doctorStudy = $studyIndividual['study'];
                $competency = $doctorStudy->getCompetency();

                if ($this->studiesOverlap($study->getDate(), $study->getEndTime(), $doctorStudy->getDate(), $doctorStudy->getEndTime())) {
                    return false;
                }

                //Если интервал времени больше рабочего дня
                $hoursPerShift = $doctorWorkSchedule->getHoursPerShift();
                $currentHoursShift = $doctorStudy->getEndTime()->diff($study->getDate())->h;
                if ($currentHoursShift >= $hoursPerShift ||
                    $study->getEndTime()->diff($doctorStudy->getDate())->d >= $hoursPerShift) {
                    return false;
                }

                // Если есть пересечение с уже назначенными приемами
                $doctorStudyEndTime = $doctorStudy->getEndTime();
                if ($doctorStudy->getDate() <= $study->getEndTime() && $study->getDate() <= $doctorStudyEndTime) {
                    return false;
                }

                //$durationSum += $competency->getDuration();
                $coefficient += $competency->getCoefficient();
            }
        }

        // Проверяем итоговую сумму времени приемов + если прибавим текущее исследование
        /*$doctorWorkTimePerDayInMinutes = $doctorWorkSchedule->getHoursPerShift() * 60;

        if (($durationSum + $study->getCompetency()->getDuration()) > $doctorWorkTimePerDayInMinutes) {
            return false;
        }*/

        // Проверяем, не нарушен ли коэффициент облучения за смену
        if ($this->maxCoefficientPerShift <= $coefficient) {
            return false;
        }

        return true;
    }

    private function evaluateFitness(array $individual): int
    {
        $fitness = 0;
        foreach ($individual as $gene) {
            if ($gene['doctor'] !== null) {
                $fitness += 1; // Увеличиваем приспособленность за каждое корректное назначение
                //В этой функции в целом надо будет добавить эвристические методы. Потому что сейчас по сути в дефолте сравнивается,
                // что назначение для исследования есть и, соответственно, вернется вариант с наименьшим количеством пропусков врачей.
                // Надо дорабатывать эту функцию различными проверками. Чтобы врач не был перенагружен
                // Думаю дальше, в основном, работа будет вестись в калибровке входных данных (их дополнении) и в этой функции
                // Возможно так же усложнение условий при создании гена / особи.
                // Но в основном тут все остальные функции (создание гена и особи в том числе) тут абстрактные, поэтому нас удовлетворяют
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

        $timeRes = [];

        foreach ($bestIndividual as $gene) {
            if ($gene['doctor'] !== null) {
                /** @var Studies $study */
                $study = $gene['study'];
                /** @var Doctor $doctor */
                $doctor = $gene['doctor'];
                $timeRes[$doctor->getId()][] = $study->getDate()->format('Y-m-d H:i') . ' - ' . $study->getEndTime()->format('Y-m-d H:i');
            }
        }
dd($bestFitness, $timeRes);
        return $bestIndividual;
    }
}