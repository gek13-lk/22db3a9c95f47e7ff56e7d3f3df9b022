<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240614192553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE calendar ADD holiday_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN calendar.holiday_name IS \'Название праздника/выходного\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE calendar DROP holiday_name');
    }
}
