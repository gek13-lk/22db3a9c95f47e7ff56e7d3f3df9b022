<?php

namespace App\Controller;

use App\Entity\TempSchedule;
use App\Modules\Algorithm\AlgorithmService;
use App\Modules\Algorithm\AlgorithmWeekService;
use App\Modules\Algorithm\DataService;
use App\Modules\Algorithm\SetTimeAlgorithmService;
use App\Modules\Navbar\DefaultNavItem;
use App\Modules\Navbar\NavElementInterface;
use App\Modules\Navbar\NavItemInterface;
use App\Repository\CalendarRepository;
use App\Repository\DoctorRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ScheduleController extends DashboardController {
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
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        $form = $this->createFormBuilder()
            ->add('month', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('schedule/run.html.twig', [
                'title' => 'Построить график',
                'form' => $form->createView(),
            ]);
        }

        $data = $form->getData();
        $dateStart = clone $data['month']->modify('first day of this month');
        $dateEnd = $data['month']->modify('last day of this month');

        //$countSchedule = 1;
        //$service3->run($dateStart, $dateEnd, $countSchedule);

        return $this->render('schedule/run.html.twig', [
            'title' => 'Построить график',
            'form' => $form->createView(),
            'calendars' => $calendarRepository->getRange($dateStart, $dateEnd),
            'doctors' => $doctorRepository->findAll(),
            'scheduleId' => 6 // TODO: получать из алгоритма
        ]);
    }
}
