<?php

namespace App\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class MigrationFactoryDecorator implements MigrationFactory
{
    public function __construct(private MigrationFactory $migrationFactory, private PasswordHasherFactoryInterface $hasher)
    {
    }

    public function createVersion(string $migrationClassName): AbstractMigration
    {
        $instance = $this->migrationFactory->createVersion($migrationClassName);

        if ($instance instanceof HasherInterface) {
            $instance->setHasher($this->hasher);
        }

        return $instance;
    }
}