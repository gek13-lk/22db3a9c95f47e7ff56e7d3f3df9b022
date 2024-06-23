<?php

declare(strict_types=1);

namespace App\Listener;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: NotificationEvent::NAME, method: 'execute')]
class NotificationEventListener
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function execute(NotificationEvent $event): void
    {
        $notification = (new Notification())
            ->setUser($event->getUser())
            ->setText($event->getText())
            ->setLink($event->getLink())
            ->setDate(new \DateTime());

        $this->em->persist($notification);
        $this->em->flush();
    }
}
