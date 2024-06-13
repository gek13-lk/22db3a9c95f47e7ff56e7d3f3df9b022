<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\MLService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeekStudiesController extends AbstractController
{
    public function __construct(public MLService $service)
    {
    }

    #[Route('/train', methods: 'POST')]
    public function trainModel(): Response
    {
        try {
            $this->service->createLog();

            return new Response('start models training');
        } catch (\Exception $e) {
            return new Response($e->getMessage(), 500);
        }
    }
}
