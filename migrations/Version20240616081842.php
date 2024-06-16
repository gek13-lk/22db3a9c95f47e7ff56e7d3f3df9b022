<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240616081842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE temp_schedule_week_studies ADD predicated_week_studies_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE temp_schedule_week_studies ADD CONSTRAINT FK_16DFB6AC511CF70F FOREIGN KEY (predicated_week_studies_id) REFERENCES week_studies_predicted (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_16DFB6AC511CF70F ON temp_schedule_week_studies (predicated_week_studies_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE temp_schedule_week_studies DROP CONSTRAINT FK_16DFB6AC511CF70F');
        $this->addSql('DROP INDEX IDX_16DFB6AC511CF70F');
        $this->addSql('ALTER TABLE temp_schedule_week_studies DROP predicated_week_studies_id');
    }
}
