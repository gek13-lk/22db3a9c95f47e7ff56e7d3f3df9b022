<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Messenger\Message\MLMessage;
use App\Modules\Algorithm\DataService;
use App\Modules\Algorithm\ExportService;
use App\Service\MLService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class WeekStudiesController extends AbstractController
{
    public function __construct(
        public MLService $service,
        public MessageBusInterface $bus,
        public ExportService $exportService,
        private DataService $dataService,
    ) {
    }

    #[Route('/train', name: 'training_model', methods: 'POST')]
    public function trainModel(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $logs = $this->service->createLog($user);
            $this->bus->dispatch(new MLMessage($logs->getId()));

            return new Response('start models training');
        } catch (\Exception $e) {
            return new Response($e->getMessage(), 500);
        }
    }

    #[Route('/train/export', name: 'train_export')]
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

        return $this->render('train/train.html.twig', [
            'title' => 'Расписание',
            'form' => $form->createView(),
            'date' => $dateStart
        ]);
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

    #[Route('/train/export/csv/{date}', name: 'train_export_csv')]
    public function exportCsv(\DateTime $date): Response
    {
        $data = $this->dataService->getPredictedData($date);

        $file = $this->exportService->exportCsv($data);

        return $this->file($file, 'train.csv');
    }

    #[Route('/train/export/xlsx/{date}', name: 'train_export_xlsx')]
    public function exportXlsx(\DateTime $date): Response
    {
        $data = $this->dataService->getPredictedData($date);

        $file = $this->exportService->exportXlsx($data);

        return $this->file($file, 'train.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
