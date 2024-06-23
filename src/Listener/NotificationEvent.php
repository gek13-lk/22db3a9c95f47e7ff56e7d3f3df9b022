<?php

declare(strict_types=1);

namespace App\Listener;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class NotificationEvent extends Event
{
    public const NAME = 'notification';

    public function __construct(
        private User $user,
        private string $text,
        private ?string $link = null
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getLink(): string
    {
        return $this->link;
    }
}
