<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240612083104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE temp_doctor_schedule ADD work_time_start TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE temp_doctor_schedule ADD work_time_end TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE temp_doctor_schedule DROP start_work_time');
        $this->addSql('ALTER TABLE temp_doctor_schedule DROP end_work_time');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE temp_doctor_schedule ADD start_work_time VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE temp_doctor_schedule ADD end_work_time VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE temp_doctor_schedule DROP work_time_start');
        $this->addSql('ALTER TABLE temp_doctor_schedule DROP work_time_end');
    }
}
