<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240609050801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doctor
  ALTER COLUMN addon_competencies
  SET DATA TYPE jsonb
  USING addon_competencies::jsonb');
        $this->addSql('ALTER TABLE doctor
  ALTER COLUMN main_competencies
  SET DATA TYPE jsonb
  USING main_competencies::jsonb');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
