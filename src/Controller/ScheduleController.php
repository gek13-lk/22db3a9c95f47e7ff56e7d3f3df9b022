<?php

namespace App\Controller;

use App\Entity\TempSchedule;
use App\Modules\Algorithm\AlgorithmWeekService;
use App\Modules\Algorithm\DataService;
use App\Modules\Algorithm\ExportService;
use App\Modules\Algorithm\SetTimeAlgorithmService;
use App\Repository\CalendarRepository;
use App\Repository\DoctorRepository;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

class ScheduleController extends DashboardController {

    public function __construct(
        private AlgorithmWeekService $algorithmService,
        private SetTimeAlgorithmService $timeAlgorithmService,
        private CalendarRepository $calendarRepository,
        private DoctorRepository $doctorRepository,
        private DataService $dataService,
        private ExportService $exportService
    )
    {
    }

    #[Route('/schedule', name: 'app_schedule')]
    public function schedule(Request $request): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $start = $request->get('dateStart', '2024-01-01');
        $end = $request->get('dateEnd', '2024-01-31');

        $dateStart = \DateTime::createFromFormat('Y-m-d', $start);
        $dateEnd = \DateTime::createFromFormat('Y-m-d', $end);

        if ($this->isGranted('ROLE_HR') || $this->isGranted('ROLE_MANAGER')) {
            $doctors = $this->doctorRepository->findAll();
        } else {
            $doctors = $this->doctorRepository->findBy(['id' => 1]); // TODO: сделать связку пользователя с врачом
        }

        return $this->render('schedule/index.html.twig', [
            'title' => 'Расписание',
            'calendars' => $this->calendarRepository->getRange($dateStart, $dateEnd),
            'doctors' => $doctors,
        ]);
    }

    #[Route('/schedule/run', name: 'app_schedule_run')]
    public function run(Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

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

        $this->algorithmService->run($dateStart, $dateEnd, $data['count']);

        return $this->render('schedule/run.html.twig', [
            'form' => $form->createView(),
            'calendars' => $this->calendarRepository->getRange($dateStart, $dateEnd),
            'doctors' => $this->doctorRepository->findAll(),
            'scheduleId' => 6 // TODO: получать из алгоритма
        ]);
    }

    #[Route('/schedule/export/csv/{tempSchedule}', name: 'app_schedule_export_csv')]
    public function exportCsv(TempSchedule $tempSchedule): Response
    {
        $data = $this->dataService->getScheduleById($tempSchedule);

        $file = $this->exportService->exportCsv($data);

        return $this->file($file, 'schedule.csv');
    }

    #[Route('/schedule/export/xlsx/{tempSchedule}', name: 'app_schedule_export_xlsx')]
    public function exportXlsx(TempSchedule $tempSchedule): Response
    {
        $data = $this->dataService->getScheduleById($tempSchedule);

        $file = $this->exportService->exportXlsx($data);

        return $this->file($file, 'schedule.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
