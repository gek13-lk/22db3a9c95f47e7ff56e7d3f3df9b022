<?php

declare(strict_types=1);

namespace App\Modules\Algorithm;

use App\Entity\Competencies;
use App\Entity\Doctor;
use App\Entity\PredictedWeekStudies;
use App\Entity\Studies;
use App\Entity\TempDoctorSchedule;
use App\Entity\TempSchedule;
use App\Entity\WeekStudies;
use App\Repository\CalendarRepository;
use App\Repository\DoctorRepository;
use App\Repository\TempDoctorScheduleRepository;
use App\Service\PredictionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class DataService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private CalendarRepository $calendarRepository,
        private DoctorRepository $doctorRepository,
        private TempDoctorScheduleRepository $tempDoctorScheduleRepository,
        private Security $security,
        private PredictionService $predictionService
    )
    {
    }

    public function initializeDb(): void
    {
        $data = file_get_contents(__DIR__ . '/mocks/generatedData.json', true);
        $data = json_decode($data, true);
        /*foreach ($data['norms'] as $competenciesData) {
            $competencies = new Competencies();
            $competencies
                ->setNorms($competenciesData['Норма в смену'])
                ->setModality($competenciesData['Модальность'])
                ->setType($competenciesData['Вид исследования']);

            $this->entityManager->persist($competencies);
        }

        $this->entityManager->flush();*/

        foreach ($data['doctors'] as $doctorData) {
            $doctor = new Doctor();
            $doctor->setMiddlename($doctorData['ID']);

            $this->entityManager->persist($doctor);

            foreach ($doctorData["Компетенции"]["Модальности"] as $modality) {
                $competencies = $this->entityManager->getRepository(Competencies::class)->findOneByTypeOrModality($modality);
                $doctor->addSpeciality($competencies);
            }

            foreach ($doctorData["Компетенции"]["Виды исследований"] as $type) {
                $competencies = $this->entityManager->getRepository(Competencies::class)->findOneByTypeOrModality(type: $type);
                $doctor->addSpeciality($competencies);
            }
        }

        $this->entityManager->flush();
    }

    public function generateInputData(): void
    {
        set_time_limit(600);
        //TODO: Перенести в БД входные данные
        $start_date = new \DateTime("2024-05-17");
        $end_date = new \DateTime("2024-05-24");
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($start_date, $interval, $end_date);

        // Модальности и виды исследований
        $competencies = $this->entityManager->getRepository(Competencies::class)->findAll();

        $studies = [];

        foreach ($period as $date) {
            foreach ($competencies as $competency) {
                $num_studies = rand(0, 10);
                if ($num_studies > 0) {
                    for ($i = 1; $i <= $num_studies; $i++) {
                        $studiesDate = (new \DateTime())
                            ->setTimestamp($date->getTimestamp())
                            ->modify('+ ' . rand(0, 23) . ' hours')
                            ->modify('+ ' . rand(0, 59) . ' minutes');
                        $studies[] = [
                            'Дата' => $studiesDate->format('Y-m-d H:i'),
                            'Модальность' => $competency->getModality(),
                            'Вид исследования' => $competency->getType(),
                        ];

                        $studyEntity = new Studies();
                        $studyEntity->setCompetency($competency);
                        $studyEntity->setDate($studiesDate);

                        $this->entityManager->persist($studyEntity);
                    }
                }
            }
        }

        /*$studiesByDay = [];

        foreach ($studiesByWeek as $studies) {
            for ($i = 1; $i <= $studies['Количество исследований']; $i++) {
                $studiesByDay[] = [
                    'Дата' => $this->getRandomDateInPeriod($studies['Дата'], $studies['Дата']->modify('+7 days')),
                    'Модальность' => $studies['Модальность'],
                    'Вид исследования' => $studies['Вид исследования'],
                ];
            }
        }*/

        // Нормы количества описанных исследований на одного врача в смену
        /*$norms = [
            ['Модальность' => 'X-ray', 'Вид исследования' => 'Ортопедические', 'Норма в смену' => 40],
            ['Модальность' => 'X-ray', 'Вид исследования' => 'Неврологические', 'Норма в смену' => 30],
            ['Модальность' => 'CT', 'Вид исследования' => 'Абдоминальные', 'Норма в смену' => 20],
            ['Модальность' => 'MRI', 'Вид исследования' => 'Кардиологические', 'Норма в смену' => 15],
            ['Модальность' => 'US', 'Вид исследования' => 'Грудные', 'Норма в смену' => 25],
            ['Модальность' => 'PET', 'Вид исследования' => 'Другие', 'Норма в смену' => 10],
            // Добавьте остальные комбинации
        ];*/

        // Список врачей с описанием их компетенций
        /*$doctors = [
            ['ID' => 'Врач 1', 'Компетенции' => ['Модальности' => ['X-ray', 'CT'], 'Виды исследований' => ['Ортопедические', 'Абдоминальные']]],
            ['ID' => 'Врач 2', 'Компетенции' => ['Модальности' => ['MRI', 'US'], 'Виды исследований' => ['Неврологические', 'Кардиологические']]],
            ['ID' => 'Врач 3', 'Компетенции' => ['Модальности' => ['PET'], 'Виды исследований' => ['Грудные', 'Другие']]],
            // Добавьте остальных врачей
        ];*/

        $this->entityManager->flush();

        file_put_contents(
            __DIR__ . '/mocks/generatedData.json',
            json_encode($studies, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        //'Нормы' => json_encode($norms, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        //'Врачи' => json_encode($doctors, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    private function getRandomDateInPeriod(\DateTime $firstDate, \DateTime $secondDate): string
    {
        if ($firstDate < $secondDate) {
            return date('Y-m-d', mt_rand($firstDate->getTimestamp(), $secondDate->getTimestamp()));
        }

        return date('Y-m-d', mt_rand($secondDate->getTimestamp(), $firstDate->getTimestamp()));
    }

    public function getScheduleById(TempSchedule $tempSchedule, bool $onlyMy = false)
    {
        $doctorSchedules = $this->tempDoctorScheduleRepository->findByTempSchedule($tempSchedule);

        $dateStart = (clone $doctorSchedules[0]->getDate())->modify('first day of this month');
        $dateEnd = $doctorSchedules[0]->getDate()->modify('last day of this month');

        $calendars = $this->calendarRepository->getRange($dateStart, $dateEnd);

        $title[] = 'Врач';
        $title[] = 'Модальность';
        $title[] = 'Дополнительные модальности';
        $title[] = 'Ставка';
        $title[] = 'Таб';

        $i = 1;
        foreach ($calendars as $calendar) {
            $title[] = $calendar->getDate()->format('d.m.Y');
            if($i === 15) {
                $title[] = 'Итого за 1 пол. месяца';
            }
            $i++;
        }
        $title[] = 'Итого за 2 пол. месяца';
        $title[] = 'Норма часов по графику';
        $title[] = 'Норма часов за полный месяц';

        if ($onlyMy) {
            $doctors = $this->doctorRepository->findBy(['user' => $this->security->getUser()]);
        } else {
            $doctors = $this->doctorRepository->findAll();
        }

        /** @var Doctor $doctor */
        foreach ($doctors as $doctor) {
            $workHours = 0;
            $allWorkHours = 0;
            $scheduleData[$doctor->getId()] = [
                'doctor' => $doctor->getFio() ?? $doctor->getId(),
                'modality' =>  \implode(',',  $doctor->getCompetency()),
                'addonModality' =>  \implode(',',  $doctor->getAddonCompetencies()),
                "stavka" => $doctor->getStavka(),
                "tab" => ""
            ];
            $doctorSchedules = $this->tempDoctorScheduleRepository->findByTempScheduleAndDoctor($tempSchedule->getId(), $doctor->getId());
            $i = 1;
            foreach ($calendars as $calendar) {
                $sch = array_filter($doctorSchedules, fn(TempDoctorSchedule $sch) => $sch->getDate() == $calendar->getDate());
                if(!empty($sch)){
                    /** @var TempDoctorSchedule $doctorSchedule */
                    foreach ($sch as $doctorSchedule) {
                        $schedule = [$doctorSchedule->getTempScheduleWeekStudies()->getWeekStudies()->getCompetency()->getModality(),
                            ' с ' . $doctorSchedule->getWorkTimeStart()->format('d.m.Y H:m:s') ?? '---',
                            ' до ' . $doctorSchedule->getWorkTimeEnd()->format('d.m.Y H:m:s') ?? '---',
                            ($doctorSchedule->getWorkHours() ?? '---') . ' часов',
                            ($doctorSchedule->getOffMinutes() ?? '---'). ' минут',
                        ];
                        $scheduleData[$doctor->getId()][$calendar->getDate()->format('d.m.Y')] = \implode(' ', $schedule);
                        $workHours += $doctorSchedule->getWorkHours() ?? 0;
                    }
                } else {
                    $scheduleData[$doctor->getId()][$calendar->getDate()->format('d.m.Y')] = null;
                }
                if($i == 15) {
                    $scheduleData[$doctor->getId()]['Итого за 1 пол. месяца'] = $workHours;
                    $allWorkHours += $workHours;
                    $workHours = 0;
                }
                $i++;
            }

            $scheduleData[$doctor->getId()]['Итого за 2 пол. месяца'] = $workHours;
            $scheduleData[$doctor->getId()]['Норма часов по графику'] = $allWorkHours + $workHours;
            $scheduleData[$doctor->getId()]['Норма часов за полный месяц'] = 155;
        }

        return (array_merge([$title], $scheduleData));
    }

    public function approveSchedule(TempSchedule $tempSchedule): void
    {
        $oldSchedules = $this->entityManager->getRepository(TempSchedule::class)->findBy(['date' => $tempSchedule->getDate()]);

        foreach (array_filter($oldSchedules, fn(TempSchedule $sch) => $sch->getId() !== $tempSchedule->getId()) as $oldSchedule) {
            $oldSchedule->setIsApproved(false);
            $this->entityManager->persist($oldSchedule);
        }

        $tempSchedule->setIsApproved(true);
        $this->entityManager->persist($tempSchedule);

        $this->entityManager->flush();
    }

    public function getPredictedData(\DateTime $date)
    {
        $weekNumbers = $this->entityManager->getRepository(PredictedWeekStudies::class)->findBy([
            'weekNumber' => $date->format('m'),
            'year' => $date->format('Y'),
            'isNew' => true
        ]);

        if (!empty($weekNumbers)) {
            $result = $weekNumbers;
        } else {
            $result = $this->predictionService->getPredictedDataByDate($date);
        }

        $weekStudies = [];

        /** @var WeekStudies $value */
        foreach ($result as $value) {
            $weekStudies[$value->getId()]['competency'] = $value->getCompetency()->getModality();
            $weekStudies[$value->getId()]['weekNumber'] = $value->getWeekNumber();
            $weekStudies[$value->getId()]['year'] = $value->getYear();
            $weekStudies[$value->getId()]['count'] = $value->getCount();
        }

        $title[] = 'Модальность';
        $title[] = 'Номер недели';
        $title[] = 'Год';
        $title[] = 'Количество';

        return (array_merge([$title], $weekStudies));
    }
}