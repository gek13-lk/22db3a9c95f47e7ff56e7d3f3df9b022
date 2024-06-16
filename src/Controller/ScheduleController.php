<?php

namespace App\Controller;

use App\Entity\TempDoctorSchedule;
use App\Entity\TempSchedule;
use App\Modules\Algorithm\AlgorithmWeekService;
use App\Modules\Algorithm\DataService;
use App\Modules\Algorithm\ExportService;
use App\Modules\Algorithm\SetTimeAlgorithmService;
use App\Repository\CalendarRepository;
use App\Repository\DoctorRepository;
use App\Repository\TempScheduleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        private TempScheduleRepository $tempScheduleRepository,
        private DataService $dataService,
        private ExportService $exportService,
    )
    {
    }

    #[Route('/schedule', name: 'app_schedule')]
    public function schedule(Request $request): Response {
        if (!$this->isGranted('ROLE_HR') && !$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        $data = [
            'month' => '01.01.2024'
        ];

        $form = $this->createFormBuilder(options: ['attr'=>['class'=>'form-inline']])
            ->add('month', ChoiceType::class, [
                'label' => 'Месяц',
                'choices' => array_reverse(array_flip($this->getDatesFromLastTwoYears())),
                'row_attr'=>['class'=>'form-group mb-2 mr-2'],
                'label_attr'=>['class'=>'col-form-label mr-2'],
                'attr'=>[
                    'class'=>'form-control',
                    'onchange' => '$("#loading-wrapper").fadeIn(500); this.form.submit()',
                ],
                'empty_data' => '01.01.2024',
            ])
            ->getForm();

        $form->setData($data);

        $form->handleRequest($request);

        $data = $form->getData();
        $date = \DateTime::createFromFormat('d.m.Y', $data['month']);
        $dateStart = (clone $date)->modify('first day of this month');
        $dateEnd = (clone $date)->modify('last day of this month');

            $doctors = $this->doctorRepository->findAll();

        return $this->render('schedule/index.html.twig', [
            'title' => 'Расписание',
            'form' => $form->createView(),
            'calendars' => $this->calendarRepository->getRange($dateStart, $dateEnd),
            'doctors' => $doctors,
            'scheduleId' => $this->getApproveScheduleByDate($date)?->getId(),
            'can_edit' => $this->isGranted('ROLE_MANAGER') || $this->isGranted('ROLE_ADMIN') ? 1 : 0
        ]);
    }

    #[Route('/schedule/my', name: 'app_schedule_my')]
    public function my(Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_DOCTOR');

        $data = [
            'month' => '01.01.2024'
        ];

        $form = $this->createFormBuilder(options: ['attr'=>['class'=>'form-inline']])
            ->add('month', ChoiceType::class, [
                'label' => 'Месяц',
                'choices' => array_reverse(array_flip($this->getDatesFromLastTwoYears())),
                'row_attr'=>['class'=>'form-group mb-2 mr-2'],
                'label_attr'=>['class'=>'col-form-label mr-2'],
                'attr'=>[
                    'class'=>'form-control',
                    'onchange' => '$("#loading-wrapper").fadeIn(500); this.form.submit()',
                ],
                'empty_data' => '01.01.2024',
            ])
            ->getForm();

        $form->setData($data);

        $form->handleRequest($request);

        $data = $form->getData();
        $date = \DateTime::createFromFormat('d.m.Y', $data['month']);
        $dateStart = (clone $date)->modify('first day of this month');
        $dateEnd = (clone $date)->modify('last day of this month');

        $doctors = $this->doctorRepository->findBy(['user' => $this->getUser()]);

        return $this->render('schedule/my.html.twig', [
            'title' => 'Мое расписание',
            'form' => $form->createView(),
            'calendars' => $this->calendarRepository->getRange($dateStart, $dateEnd),
            'doctors' => $doctors,
            'scheduleId' => $this->getApproveScheduleByDate($date)?->getId()
        ]);
    }

    #[Route('/schedule/temp/{tempSchedule}', name: 'app_schedule_temp')]
    public function tempSchedule(TempSchedule $tempSchedule): Response {
        if (!$this->isGranted('ROLE_HR') && !$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        $date = $tempSchedule->getDate();
        $dateStart = (clone $date)->modify('first day of this month');
        $dateEnd = (clone $date)->modify('last day of this month');

        $doctors = $this->doctorRepository->findAll();

        return $this->render('schedule/schedule.html.twig', [
            'title' => 'Расписание',
            'calendars' => $this->calendarRepository->getRange($dateStart, $dateEnd),
            'doctors' => $doctors,
            'scheduleId' => $tempSchedule->getId(),
            'can_edit' => $this->isGranted('ROLE_MANAGER') || $this->isGranted('ROLE_ADMIN') ? 1 : 0
        ]);
    }

    #[Route('/schedule/approve/{tempSchedule}', name: 'app_schedule_approve')]
    public function approve(TempSchedule $tempSchedule): Response {
        if (!$this->isGranted('ROLE_HR') && !$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        $this->dataService->approveSchedule($tempSchedule);

        return $this->redirectToRoute('app_schedule_run');
    }

    #[Route('/schedule/run', name: 'app_schedule_run')]
    public function run(Request $request): Response {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        $data = [
            'month' => '01.01.2024',
            'count' => 1,
            'maxDoctorsCount' => 260,
            'isPredicated' => false
        ];

        $form = $this->createFormBuilder(options: ['attr'=>['class'=>'form-inline']])
            ->add('month', ChoiceType::class, [
                'label' => 'Месяц',
                'choices' => array_reverse(array_flip($this->getDatesFromLastTwoYears('-7month', '+2month'))),
                'row_attr'=>['class'=>'form-group mb-2 mr-2'],
                'label_attr'=>['class'=>'col-form-label mr-2'],
                'attr'=>[
                    'class'=>'form-control',
                ],
                'empty_data' => '01.01.2024',
            ])
            ->add('count', ChoiceType::class, [
                'label' => 'Сформировать расписаний',
                'choices' => [
                    '1' => 1,
                    '2' => 2,
                    '3' => 3
                ],
                'row_attr'=>['class'=>'form-group mb-2 mr-2'],
                'label_attr'=>['class'=>'col-form-label mr-2'],
                'attr'=>[
                    'class'=>'form-control',
                ],
            ])
            ->add('maxDoctorsCount', NumberType::class, [
                'label' => 'Максимальное количество врачей',
                'html5' => true,
                'row_attr'=>['class'=>'form-group mb-2 mr-2'],
                'label_attr'=>['class'=>'col-form-label mr-2'],
                'attr'=>[
                    'class'=>'form-control',
                ],
            ])
            ->add('isPredicated', ChoiceType::class, [
                'label' => 'Источник данных исследований',
                'choices' => [
                    'Исходные данные' => false,
                    'Спрогнозированные данные' => true
                ],
                'row_attr'=>['class'=>'form-group mb-2 mr-2'],
                'label_attr'=>['class'=>'col-form-label mr-2'],
                'attr'=>[
                    'class'=>'form-control',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Построить',
                'row_attr'=>[
                    'class'=>'mb-2',
                ],
            ])
            ->getForm();

        $form->setData($data);

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('schedule/run.html.twig', [
                'title' => 'Построить график',
                'form' => $form->createView(),
                'schedules' => $this->tempScheduleRepository->findBy([], ['id' => 'DESC']),
            ]);
        }

        $data = $form->getData();
        $date = \DateTime::createFromFormat('d.m.Y', $data['month']);
        $dateStart = (clone $date)->modify('first day of this month');
        $dateEnd = (clone $date)->modify('last day of this month');

        $this->algorithmService->run($dateStart, $dateEnd, $data['count'], $data['maxDoctorsCount'], $data['isPredicated']);

        return $this->render('schedule/run.html.twig', [
            'title' => 'Составленные расписания',
            'form' => $form->createView(),
            'schedules' => $this->tempScheduleRepository->findBy([], ['id' => 'DESC'])
        ]);
    }

    #[Route('/schedule/export/csv/{tempSchedule}', name: 'app_schedule_export_csv')]
    #[Route('/schedule/export/csv/{tempSchedule}/my', name: 'app_schedule_export_csv_my')]
    public function exportCsv(TempSchedule $tempSchedule, Request $request): Response
    {
        $data = $this->dataService->getScheduleById($tempSchedule, $request->get('_route') === 'app_schedule_export_csv_my');

        $file = $this->exportService->exportCsv($data);

        return $this->file($file, 'schedule.csv');
    }

    #[Route('/schedule/export/xlsx/{tempSchedule}', name: 'app_schedule_export_xlsx')]
    #[Route('/schedule/export/xlsx/{tempSchedule}/my', name: 'app_schedule_export_xlsx_my')]
    public function exportXlsx(TempSchedule $tempSchedule, Request $request): Response
    {
        $data = $this->dataService->getScheduleById($tempSchedule, $request->get('_route') === 'app_schedule_export_xlsx_my');

        $file = $this->exportService->exportXlsx($data);

        return $this->file($file, 'schedule.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }


    #[Route('/schedule/{task}/edit', name: 'app_schedule_task_edit', methods: ["PUT"])]
    public function edit(TempDoctorSchedule $task, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        $date = \DateTime::createFromFormat('Y-m-d', $request->get('date', $task->getDate()->format('Y-m-d')))->setTime(0,0);
        $timeStart = \DateTime::createFromFormat('Y-m-d H:i', $task->getDate()->format('Y-m-d') . ' ' . $request->get('timeStart', $task->getWorkTimeStart()->format('H:i')));
        $timeEnd = \DateTime::createFromFormat('Y-m-d H:i', $task->getDate()->format('Y-m-d') . ' ' . $request->get('timeEnd', $task->getWorkTimeEnd()->format('H:i')));

        if($timeEnd->getTimestamp() < $timeStart->getTimestamp()) {
            $timeEnd->modify('+1day');
        }

        $offMinutes = $request->get('offMinutes', $task->getOffMinutes());

        $minutes = ($timeEnd->getTimestamp() - $timeStart->getTimestamp()) / 60;
        $workHours = ($minutes - $offMinutes) / 60;

        $task->setDate($date);
        $task->setWorkTimeStart($timeStart);
        $task->setWorkTimeEnd($timeEnd);
        $task->setWorkHours($workHours);
        $task->setOffMinutes($offMinutes);

        $entityManager->persist($task);
        $entityManager->flush();

        return new JsonResponse();
    }

    #[Route('/schedule/{task}/delete', name: 'app_schedule_task_delete', methods: ["DELETE"])]
    public function delete(TempDoctorSchedule $task, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('нет прав');
        }

        $entityManager->remove($task);
        $entityManager->flush();

        return new JsonResponse();
    }

    function getApproveScheduleByDate(\DateTime $month): ?TempSchedule
    {
        $entity = $this->tempScheduleRepository->findOneBy(['date' => $month,], ['isApproved' => 'DESC', 'id' => 'DESC']);

        return $entity;
    }

    function getDatesFromLastTwoYears(string $modifyStart = '-2 years', string $modifyEnd = 'now') {
        $startDate = new \DateTime($modifyStart);
        $endDate = new \DateTime($modifyEnd);

        $interval = new \DateInterval('P1M');
        $dates = [];

        while ($startDate <= $endDate) {
            $formattedDate = $startDate->format('01.m.Y');
            $monthRussian = match($startDate->format('m')) {
                '01' => 'Январь',
                '02' => 'Февраль',
                '03' => 'Март',
                '04' => 'Апрель',
                '05' => 'Май',
                '06' => 'Июнь',
                '07' => 'Июль',
                '08' => 'Август',
                '09' => 'Сентябрь',
                '10' => 'Октябрь',
                '11' => 'Ноябрь',
                default => 'Декабрь',
            };
            $dates[$formattedDate] = $monthRussian . ' ' . $startDate->format('Y');
            $startDate->add($interval);
        }

        return $dates;
    }
}
