<?php

namespace App\Controller;

use App\Entity\TempSchedule;
use App\Modules\Algorithm\RecommendationService;
use App\Modules\Navbar\DefaultNavItem;
use App\Modules\Navbar\NavElementInterface;
use App\Modules\Navbar\NavItemInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RecommendationController extends AbstractController implements NavElementInterface {

    public function __construct(
        private RecommendationService $service
    ) {

    }
    #[Route('/recommendation/{tempSchedule}', name: 'app_recommendation')]
    public function index(TempSchedule $tempSchedule): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $recommendations = $this->service->getRecommendation($tempSchedule);

        return $this->render('recommendation/index.html.twig', [
            'controller_name' => 'RecommendationController',
            'username' => $user->getUserIdentifier(),
            'recommendations' => $recommendations
        ]);
    }

    public function getNavItem(): NavItemInterface {
        return new DefaultNavItem(
            'Расписание',
            '<i class="fa fa-thumbs-up"></i>',
            'app_recommendation'
        );
    }

    public static function getPriority(): int {
        return 0;
    }
}
