<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\MLService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeekStudiesController extends AbstractController
{
    public function __construct(public EntityManagerInterface $em)
    {
    }

    #[Route('/train', methods: 'POST')]
    public function trainModel(MLService $service): Response
    {
        try {
            $service->execute();

            return new Response('ok');
        } catch (\Exception $exception) {
            return new Response($exception->getMessage(), 500);
        }
    }
}
