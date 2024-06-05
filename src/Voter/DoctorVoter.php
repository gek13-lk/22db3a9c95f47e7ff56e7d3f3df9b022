<?php

namespace App\Voter;

class DoctorVoter extends AbstractVoter {

    private const PREFIX = 'DOCTOR_';

    public const LIST = self::PREFIX . 'LIST';
    public const SHOW = self::PREFIX . 'SHOW';
    public const ADD = self::PREFIX . 'ADD';
    public const EDIT = self::PREFIX . 'EDIT';
    public const REMOVE = self::PREFIX . 'REMOVE';

    protected function getTitle(string $code): string {
        return match ($code) {
            self::LIST => 'Просмотр списка врачей',
            self::SHOW => 'Просмотр врача',
            self::ADD => 'Добавление врача',
            self::EDIT => 'Изменение врача',
            self::REMOVE => 'Удаление врача',
            default => $code,
        };
    }
}