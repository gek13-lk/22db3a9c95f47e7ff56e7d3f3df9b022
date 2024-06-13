<?php

namespace App\Controller;

use App\Entity\Doctor;
use App\Entity\Role;
use App\Entity\User;
use App\Voter\DoctorVoter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_schedule');
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(): Response {
        return $this->redirectToRoute('app_schedule');
    }

    #[Route('/calendar', name: 'calendar')]
    public function calendar(): Response {
        return $this->render('dashboard/calendar.html.twig');
    }

    public function configureDashboard(): Dashboard {
        return Dashboard::new()
            ->setTitle('Референс-центр')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable {
        yield MenuItem::linkToRoute('Расписание', 'fa fa-home', 'app_schedule');
        yield MenuItem::linkToCrud('Врачи', 'fas fa-user-doctor', Doctor::class)
            ->setPermission(DoctorVoter::LIST);

        yield MenuItem::section('Администрирование')
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Пользователи', 'fas fa-users-gear', User::class)
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Роли', 'fas fa-gears', Role::class)
            ->setPermission('ROLE_ADMIN');
    }
}
