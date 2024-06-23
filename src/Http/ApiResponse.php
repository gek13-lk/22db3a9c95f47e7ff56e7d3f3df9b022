<?php

declare(strict_types=1);

namespace App\Http;

final class ApiResponse
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAIL = 'fail';
    public const STATUS_ERROR = 'error';

    public const MESSAGE_VALIDATION = 'Ошибка валидации';
    public const MESSAGE_BAD_REQUEST = 'Указаны не все обязательные параметры';
    public const MESSAGE_FORBIDDEN = 'Доступ запрещен';

    public string $status;

    public $result;

    public ?string $message;

    public function __construct($result = [], ?string $message = null, string $status = self::STATUS_SUCCESS)
    {
        $this->result = $result;
        $this->message = $message;
        $this->status = $status;
    }
}