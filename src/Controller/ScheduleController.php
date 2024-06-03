<?php

namespace App\Controller;

use App\Modules\Algorithm\AlgorithmService;
use App\Modules\Algorithm\AlgorithmWeekService;
use App\Modules\Algorithm\DataService;
use App\Modules\Navbar\DefaultNavItem;
use App\Modules\Navbar\NavElementInterface;
use App\Modules\Navbar\NavItemInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ScheduleController extends AbstractController implements NavElementInterface {

    public function __construct(private AlgorithmService $service, private AlgorithmWeekService $service3, private DataService $service2) {}
    #[Route('/schedule', name: 'app_schedule')]
    public function index(): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        //$this->service2->generateInputData();
        $this->service3->run();
        dd(1);
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
