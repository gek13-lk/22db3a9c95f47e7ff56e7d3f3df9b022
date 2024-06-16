<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240616040103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE temp_schedule ADD doctors_max_count INT DEFAULT NULL');
        $this->addSql('ALTER TABLE temp_schedule ADD date DATE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN temp_schedule.doctors_max_count IS \'Максимальное количество врачей (входные данные)\'');
        $this->addSql('COMMENT ON COLUMN temp_schedule.date IS \'Дата начала расписания\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE temp_schedule DROP doctors_max_count');
        $this->addSql('ALTER TABLE temp_schedule DROP date');
    }
}
