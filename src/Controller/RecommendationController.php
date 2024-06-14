<?php

namespace App\Controller;

use App\Entity\TempSchedule;
use App\Modules\Algorithm\RecommendationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RecommendationController extends DashboardController {
    public function index(): Response
    {
        throw new \LogicException('');
    }

    #[Route('/recommendation/{tempSchedule}', name: 'app_recommendation')]
    public function recommendation(TempSchedule $tempSchedule, RecommendationService $service): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $recommendations = $service->getRecommendation($tempSchedule);

        return $this->render('recommendation/index.html.twig', [
            'controller_name' => 'RecommendationController',
            'username' => $user->getUserIdentifier(),
            'recommendations' => $recommendations
        ]);
    }
}
