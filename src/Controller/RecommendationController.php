<?php

namespace App\Controller;

use App\Entity\TempSchedule;
use App\Modules\Algorithm\RecommendationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RecommendationController extends DashboardController {

    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('/recommendations', name: 'recommendation_list')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $schedules = $this->em->getRepository(TempSchedule::class)->findAll();

        return $this->render('recommendation/index.html.twig', [
            'schedules' => $schedules
        ]);
    }

    #[Route('/recommendation/{tempSchedule}', name: 'recommendation')]
    public function recommendation(TempSchedule $tempSchedule, RecommendationService $service): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $recommendations = $service->getRecommendation($tempSchedule);

        return $this->render('recommendation/recommendation.html.twig', [
            'recommendations' => $recommendations
        ]);
    }
}
