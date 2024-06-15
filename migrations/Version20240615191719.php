<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240615191719 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doctor_work_schedules ADD shift_start_time_hour INT DEFAULT NULL');
        $this->addSql('ALTER TABLE doctor_work_schedules ADD shift_start_time_minutes INT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN doctor_work_schedules.shift_start_time_hour IS \'Желаемое время начала смены (час)\'');
        $this->addSql('COMMENT ON COLUMN doctor_work_schedules.shift_start_time_minutes IS \'Желаемое время начала смены (Минуты)\'');
        $this->addSql('COMMENT ON COLUMN doctor_work_schedules.is_holiday_off IS \'Выходные на выходных и праздниках\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE doctor_work_schedules DROP shift_start_time_hour');
        $this->addSql('ALTER TABLE doctor_work_schedules DROP shift_start_time_minutes');
        $this->addSql('COMMENT ON COLUMN doctor_work_schedules.is_holiday_off IS \'Выходные на выходных\'');
    }
}
