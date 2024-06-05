<?php

namespace App\Voter;

interface PrivilegeGroupInterface {

    /**
     * @return array<string, string>
     */
    public function getPrivileges(): array;
}