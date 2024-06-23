<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240623020511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE temp_doctor_schedule ADD coefficient DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN temp_doctor_schedule.coefficient IS \'Планируемый коэффициент УЕ\'');

        $this->addSql('UPDATE competencies c SET max_count_per_shift = 205 WHERE c.modality = \'Денситометрия\'');
        $this->addSql('UPDATE competencies c SET max_count_per_shift = 121 WHERE c.modality = \'РГ\'');
        $this->addSql('UPDATE competencies c SET max_count_per_shift = 29 WHERE c.modality = \'МРТ\'');
        $this->addSql('UPDATE competencies c SET max_count_per_shift = 121 WHERE c.modality = \'ММГ\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE temp_doctor_schedule DROP coefficient');
    }
}
