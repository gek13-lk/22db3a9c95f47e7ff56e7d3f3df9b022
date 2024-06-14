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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ScheduleController extends AbstractController implements NavElementInterface {

    public function __construct(private EntityManagerInterface $entityManager, private AlgorithmWeekService $service3, private SetTimeAlgorithmService $timeAlgorithmService) {}
    #[Route('/schedule', name: 'app_schedule')]
    public function index(): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $countSchedule = 5;
        $user = $this->getUser();
        //$this->service3->run(new \DateTime('2024-01-01'), new \DateTime('2024-01-09'), $countSchedule);
        //$schedule = $this->entityManager->getRepository(TempSchedule::class)->find(2);

        //$this->timeAlgorithmService->setTime($schedule);
        $this->service3->run(new \DateTime('2024-01-01'), new \DateTime('2024-01-09'), $countSchedule);

        return $this->render('schedule/index.html.twig', [
            'controller_name' => 'ScheduleController',
            'username' => $user->getUserIdentifier(),
        ]);
    }

    public function getNavItem(): NavItemInterface {
        return new DefaultNavItem(
            'Расписание',
            '<i class="fa-solid fa-calendar-days"></i>',
            'app_schedule'
        );
    }

    public static function getPriority(): int {
        return 0;
    }
}
