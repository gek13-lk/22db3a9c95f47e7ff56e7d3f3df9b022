<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240613114452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE model_training_logs ALTER is_success SET DEFAULT false');
        $this->addSql('ALTER TABLE "user" ADD email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD firstname VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD surname VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD middlename VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ALTER privileges DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE model_training_logs ALTER is_success DROP DEFAULT');
        $this->addSql('ALTER TABLE "user" DROP email');
        $this->addSql('ALTER TABLE "user" DROP firstname');
        $this->addSql('ALTER TABLE "user" DROP surname');
        $this->addSql('ALTER TABLE "user" DROP middlename');
        $this->addSql('ALTER TABLE "user" ALTER privileges SET DEFAULT \'[]\'');
    }
}
