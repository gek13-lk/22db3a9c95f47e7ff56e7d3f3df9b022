<?php

declare(strict_types=1);

namespace App\Messenger\Message;

class MLMessage
{
    public function __construct(private int $logId)
    {
    }

    public function getLogId(): int
    {
        return $this->logId;
    }
}
