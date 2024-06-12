<?php

declare(strict_types=1);

namespace App\Listener;

use App\Entity\MLLogs;
use App\Service\MLService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

final class MLLogsListener
{
    public function __construct(
        private MLService $service,
        private EntityManagerInterface $em
    ) {
    }

    public function postPersist(MLLogs $entity, LifecycleEventArgs $event): void
    {
        if ($entity->isSuccess()) {
            return;
        }

        try {
            $this->service->execute();

            $entity->setIsSuccess();
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception('Ошибка обучения моделей: '.$e->getMessage());
        }
    }
}
