<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240612131319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE temp_doctor_schedule DROP CONSTRAINT fk_c8843ad696e57a11');
        $this->addSql('DROP INDEX idx_c8843ad696e57a11');
        $this->addSql('ALTER TABLE temp_doctor_schedule RENAME COLUMN temp_schedule_id TO temp_schedule_week_studies_id');
        $this->addSql('ALTER TABLE temp_doctor_schedule ADD CONSTRAINT FK_C8843AD612D0A759 FOREIGN KEY (temp_schedule_week_studies_id) REFERENCES temp_schedule_week_studies (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C8843AD612D0A759 ON temp_doctor_schedule (temp_schedule_week_studies_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE temp_doctor_schedule DROP CONSTRAINT FK_C8843AD612D0A759');
        $this->addSql('DROP INDEX IDX_C8843AD612D0A759');
        $this->addSql('ALTER TABLE temp_doctor_schedule RENAME COLUMN temp_schedule_week_studies_id TO temp_schedule_id');
        $this->addSql('ALTER TABLE temp_doctor_schedule ADD CONSTRAINT fk_c8843ad696e57a11 FOREIGN KEY (temp_schedule_id) REFERENCES temp_schedule_week_studies (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_c8843ad696e57a11 ON temp_doctor_schedule (temp_schedule_id)');
    }
}
