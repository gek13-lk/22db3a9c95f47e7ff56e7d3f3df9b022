<?php

namespace App\Doctrine\Migrations;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

interface HasherInterface {
    public function getHasher(): PasswordHasherFactoryInterface;
    public function setHasher(PasswordHasherFactoryInterface $hasher): void;
}