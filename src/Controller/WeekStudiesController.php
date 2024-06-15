<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Messenger\Message\MLMessage;
use App\Service\MLService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class WeekStudiesController extends AbstractController
{
    public function __construct(
        public MLService $service,
        public MessageBusInterface $bus
    ) {
    }

    #[Route('/train', methods: 'POST')]
    public function trainModel(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $logs = $this->service->createLog($user);
            $this->bus->dispatch(new MLMessage($logs->getId()));

            return new Response('start models training');
        } catch (\Exception $e) {
            return new Response($e->getMessage(), 500);
        }
    }
}
