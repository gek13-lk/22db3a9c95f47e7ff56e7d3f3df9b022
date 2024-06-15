<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240615093649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM doctor_addresses_reg WHERE doctor_address_id in (SELECT id FROM doctor_addresses WHERE doctor_info_id in (SELECT id FROM doctor_info WHERE doctor_id IS NULL))');
        $this->addSql('DELETE FROM doctor_addresses WHERE doctor_info_id in (SELECT id FROM doctor_info WHERE doctor_id IS NULL)');
        $this->addSql('DELETE FROM doctor_document WHERE doctor_info_id in (SELECT id FROM doctor_info WHERE doctor_id IS NULL)');
        $this->addSql('DELETE FROM doctor_info WHERE doctor_id IS NULL');
        $this->addSql('ALTER TABLE doctor_info ALTER doctor_id SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE doctor_info ALTER doctor_id DROP NOT NULL');
    }
}
