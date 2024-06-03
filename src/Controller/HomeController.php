<?php

namespace App\Controller;

use App\Modules\Navbar\DefaultNavItem;
use App\Modules\Navbar\NavElementInterface;
use App\Modules\Navbar\NavItemInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController implements NavElementInterface
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    public function getNavItem(): NavItemInterface {
        return new DefaultNavItem(
            'Главная',
            '<i class="fa-solid fa-house"></i>',
            'app_home'
        );
    }

    public static function getPriority(): int {
        return 1000;
    }
}
