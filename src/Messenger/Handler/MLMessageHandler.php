<?php

declare(strict_types=1);

namespace App\Messenger\Handler;

use App\Entity\MLLogs;
use App\Messenger\Message\MLMessage;
use App\Service\MLService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class MLMessageHandler
{
    public function __construct(
        private MLService $service,
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(MLMessage $message): void
    {
        try {
            /** @var MLLogs $log */
            $log = $this->em->getRepository(MLLogs::class)->find($message->getLogId());
            $log->setIsSuccess();
            $this->em->flush();

            $this->service->execute();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
