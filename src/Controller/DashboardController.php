<?php

namespace App\Controller;

use App\Entity\Calendar;
use App\Entity\Competencies;
use App\Entity\Doctor;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\WeekStudies;
use App\Enum\Holiday;
use App\Voter\DoctorVoter;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\MenuItemDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;
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
    public function calendar(EntityManagerInterface $em, Security $security): Response {
        /** @var User $user */
        $user = $security->getUser();

        $workingDate = $this->getDefaultWorkingDay();
        $workingDay = $workingDate->format('d');
        $workingMonth = Holiday::findMonth($workingDate->format('m'));

        $currentDate = new \DateTime();
        $calendar = $em->getRepository(Calendar::class)->findBy(['god' => $currentDate->format('Y')]);

        foreach ($calendar as $date) {
            if (1 === $date->getHoliday()) {
                $calendarEvents[] = [
                    'title' => $date->getHolidayName() ?? 'Выходной',
                    'start' => $date->getDate()->format('Y-m-d'),
                    'end' => $date->getDate()->format('Y-m-d'),
                ];
            }
        }

        return $this->render('dashboard/calendar.html.twig', array_merge(
            Holiday::findNextHolidays(), [
            'workingDay' => $workingDay,
            'workingMonth' => $workingMonth,
            'calendarEvents' => $calendarEvents ?? [],
        ]));
    }

    private function getDefaultWorkingDay(): \DateTime
    {
        $currentDate = (new \DateTime())->modify('+3 hours');
        while (true) {
            if ($currentDate->format('N') < 6) {
                return $currentDate;
            }

            $currentDate->modify('+1 day');
        }
    }

    public function configureDashboard(): Dashboard {
        return Dashboard::new()
            ->setTitle('Референс-центр. Графики')
            ->renderContentMaximized();
    }

    public function configureUserMenu(UserInterface $user): UserMenu {

        $userMenuItems = [
            MenuItem::linkToCrud('Профиль', 'icon-user1', User::class)
                ->setAction('detail')
                ->setEntityId($this->getUser()->getId()),

        ];

        if (class_exists(LogoutUrlGenerator::class)) {
            $userMenuItems[] = MenuItem::section();
            $userMenuItems[] = MenuItem::linkToLogout(t('user.sign_out', domain: 'EasyAdminBundle'), 'icon-log-out1');
        }

        $userName = method_exists($user, '__toString') ? (string) $user : $user->getUserIdentifier();

        return UserMenu::new()
            ->displayUserName()
            ->displayUserAvatar()
            ->setName($userName)
            ->setAvatarUrl(null)
            ->setMenuItems($userMenuItems);
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
                MenuItem::linkToRoute('Составить график', null, 'app_schedule_run')
                    ->setPermission('ROLE_MANAGER'),
            ]);

        yield MenuItem::subMenu('Методы визуализации', 'icon-vibration')
            ->setSubItems([
                MenuItem::linkToCrud('Справочник', null, Competencies::class),
                MenuItem::linkToCrud('История по неделям', null, WeekStudies::class),
            ]);

        yield MenuItem::subMenu('Рекомендации', 'fa fa-thumbs-up')
            ->setSubItems([
                MenuItem::linkToRoute('Рекомендации', null, 'recommendation_list'),
            ]);
    }
}
