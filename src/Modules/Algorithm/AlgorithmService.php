<?php

declare(strict_types=1);

namespace App\Modules\Algorithm;

use App\Entity\Competencies;
use App\Entity\Doctor;
use Doctrine\ORM\EntityManagerInterface;

class AlgorithmService
{
    private int $populationSize = 100;
    private float $mutationRate = 0.01;
    private float $crossoverRate = 0.7;
    private int $generations = 100;
    private array $population = [];

    /** @var Doctor[] */
    private array $doctors;
    private array $studies;

    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    private function initializeEnv(): void
    {
        $this->doctors = $this->entityManager->getRepository(Doctor::class)->findAll();
        $studiesJson = file_get_contents(__DIR__.'/mocks/generatedData.json', true);
        $this->studies = json_decode($studiesJson, true);

    }
    public function run(): void
    {
        set_time_limit(600);
        ini_set('memory_limit', '512M');

        $this->initializeEnv();
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
            //Находим врачей, которые могут взяться за это исследование
            $availableDoctors = array_filter($this->doctors, function ($doctor) use ($study, $individual) {
                /** @var Competencies|null $doctorCompetency */
                $doctorCompetency = $this->entityManager->getRepository(Competencies::class)->findByDoctor(
                    $doctor,
                    $study['Вид исследования'],
                    $study['Модальность']
                );

                return
                    $doctorCompetency &&
                    $this->hasAvailableTime($doctor, $doctorCompetency->getDuration()) &&
                    $this->isNorm(
                        $doctor,
                        $study,
                        $individual
                    );
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

    private function createRandomGene(): array
    {
        $study = $this->studies[array_rand($this->studies)];
        $availableDoctors = array_filter($this->doctors, function ($doctor) use ($study) {
            /** @var Competencies|null $doctorCompetency */
            $doctorCompetency = $this->entityManager->getRepository(Competencies::class)->findByDoctor(
                $doctor,
                $study['Вид исследования'],
                $study['Модальность']
            );

            return
                $doctorCompetency &&
                $this->hasAvailableTime($doctor, $doctorCompetency->getDuration()) &&
                $this->isNorm(
                    $doctor,
                    $study['Вид исследования'],
                    $study['Модальность']
                );
        });

        if (!empty($availableDoctors)) {
            $selectedDoctor = $availableDoctors[array_rand($availableDoctors)];
            return ['study' => $study, 'doctor' => $selectedDoctor];
        } else {
            return ['study' => $study, 'doctor' => null];
        }
    }

    //Проверка по времени приема
    private function hasAvailableTime(Doctor $doctor, int $duration): bool
    {
        //Доработать алгоритм анализа осталось ли время для приема!
        return array_sum($doctor->getAvailableHours()) >= $duration;
    }

    //Проверка на превышение нормы исследований (по облучению)
    private function isNorm(Doctor $doctor, array $study, array $individual): bool
    {
        dd($doctor, $individual, $study);
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
                // + проверка, чтобы врач по нормам получал количество излучения
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