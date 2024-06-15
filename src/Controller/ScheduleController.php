<?php

namespace App\Controller;

use App\Entity\Doctor;
use App\Entity\TempDoctorSchedule;
use App\Entity\TempSchedule;
use App\Modules\Algorithm\AlgorithmWeekService;
use App\Modules\Algorithm\SetTimeAlgorithmService;
use App\Repository\CalendarRepository;
use App\Repository\DoctorRepository;
use App\Repository\TempDoctorScheduleRepository;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

class ScheduleController extends DashboardController {

    public function __construct(
        private CalendarRepository $calendarRepository,
        private DoctorRepository $doctorRepository,
        private TempDoctorScheduleRepository $tempDoctorScheduleRepository,
        private AlgorithmWeekService $service3,
        private SetTimeAlgorithmService $timeAlgorithmService
    )
    {
    }

    #[Route('/schedule', name: 'app_schedule')]
    public function schedule(Request $request, CalendarRepository $calendarRepository, DoctorRepository $doctorRepository,
        AlgorithmWeekService $service3, SetTimeAlgorithmService $timeAlgorithmService): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $start = $request->get('dateStart', '2024-01-01');
        $end = $request->get('dateEnd', '2024-01-31');

        $dateStart = \DateTime::createFromFormat('Y-m-d', $start);
        $dateEnd = \DateTime::createFromFormat('Y-m-d', $end);

        if ($this->isGranted('ROLE_HR') || $this->isGranted('ROLE_MANAGER')) {
            $doctors = $doctorRepository->findAll();
        } else {
            $doctors = $doctorRepository->findBy(['id' => 1]); // TODO: сделать связку пользователя с врачом
        }

        return $this->render('schedule/index.html.twig', [
            'title' => 'Расписание',
            'calendars' => $calendarRepository->getRange($dateStart, $dateEnd),
            'doctors' => $doctors,
        ]);
    }

    #[Route('/schedule/run', name: 'app_schedule_run')]
    public function run(Request $request, AlgorithmWeekService $service3, CalendarRepository $calendarRepository,
        DoctorRepository $doctorRepository): Response {
//        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        $form = $this->createFormBuilder()
            ->add('month', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('count', NumberType::class, [
                'html5' => true,
            ])
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('schedule/run.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        $data = $form->getData();
        $dateStart = clone $data['month']->modify('first day of this month');
        $dateEnd = $data['month']->modify('last day of this month');

        $service3->run($dateStart, $dateEnd, $data['count']);

        return $this->render('schedule/run.html.twig', [
            'form' => $form->createView(),
            'calendars' => $calendarRepository->getRange($dateStart, $dateEnd),
            'doctors' => $doctorRepository->findAll(),
            'scheduleId' => 6 // TODO: получать из алгоритма
        ]);
    }

    public function getScheduleById(TempSchedule $tempSchedule)
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

        $doctors = $this->doctorRepository->findAll();

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

    #[Route('/schedule/export/{tempSchedule}', name: 'app_schedule_export_csv')]
    public function exportCsv(TempSchedule $tempSchedule): Response
    {
        $data = $this->getScheduleById($tempSchedule);

        $response = new StreamedResponse();

        $response->setCallback(function () use ($data) {
            $handle = fopen('php://output', 'wb');

            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        });

        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

        return $response;
    }
}
