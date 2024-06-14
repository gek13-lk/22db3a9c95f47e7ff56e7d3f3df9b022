<?php

namespace App\Controller;

use App\Entity\Doctor;
use App\Entity\Role;
use App\Entity\User;
use App\Voter\DoctorVoter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use function Symfony\Component\Translation\t;

class DashboardController extends AbstractDashboardController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_schedule');
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(AdminUrlGenerator $urlGenerator): Response {
        return $this->redirect($urlGenerator->setRoute('app_schedule')->generateUrl());
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

    public function configureUserMenu(UserInterface $user): UserMenu {
        return parent::configureUserMenu($user)->addMenuItems([
            MenuItem::linkToRoute('Профиль', null, 'app_profile'),
        ]);
    }

    public function configureMenuItems(): iterable {
        yield MenuItem::linkToRoute('Расписание', 'icon-home', 'app_schedule');

        yield MenuItem::subMenu('Администрирование', 'icon-settings')
            ->setPermission('ROLE_ADMIN')
            ->setSubItems([
                MenuItem::linkToCrud('Пользователи', null, User::class)->setPermission('ROLE_ADMIN'),
                MenuItem::linkToCrud('Роли', null, Role::class)->setPermission('ROLE_ADMIN'),
            ]);

        yield MenuItem::subMenu('Сотрудники', 'icon-contact_mail')
            ->setPermission(new Expression('"ROLE_ADMIN" in role_names or "ROLE_HR" in role_names or "ROLE_MANAGER" in role_names'))
            ->setSubItems([
                MenuItem::linkToCrud('Врачи', null, Doctor::class)
                    ->setPermission(DoctorVoter::LIST)
            ]);

        yield MenuItem::subMenu('График', 'icon-calendar')
            ->setSubItems([
                MenuItem::linkToRoute('Календарь', null, 'calendar'),
                MenuItem::linkToRoute('Составить график', null, 'app_schedule_run'),
            ]);

    }
}
