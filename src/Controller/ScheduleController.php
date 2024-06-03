<?php

namespace App\Controller;

use App\Modules\Navbar\DefaultNavItem;
use App\Modules\Navbar\NavElementInterface;
use App\Modules\Navbar\NavItemInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ScheduleController extends AbstractController implements NavElementInterface {
    #[Route('/schedule', name: 'app_schedule')]
    public function index(): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

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
