<?php

namespace App\Controller;

use App\Entity\Doctor;
use App\Entity\Role;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController {
    #[Route('/dashboard', name: 'dashboard')]
    public function index(): Response {
        return $this->render('dashboard/index.html.twig', [
            'content_title' => 'Главная',
        ]);
    }

    public function configureDashboard(): Dashboard {
        return Dashboard::new()
            ->setTitle('App');
    }

    public function configureMenuItems(): iterable {
        yield MenuItem::linkToDashboard('Главная', 'fa fa-home');
        yield MenuItem::linkToCrud('Врачи', 'fas fa-list', Doctor::class);

        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::section('Администрирование');
            yield MenuItem::linkToCrud('Пользователи', 'fas fa-users', User::class);
            yield MenuItem::linkToCrud('Роли', 'fas fa-users', Role::class);
        }
    }
}
