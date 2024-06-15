<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240615084024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE doctor ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE doctor DROP surname');
        $this->addSql('ALTER TABLE doctor DROP firstname');
        $this->addSql('ALTER TABLE doctor DROP middlename');
        $this->addSql('ALTER TABLE doctor ADD CONSTRAINT FK_1FC0F36AA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FC0F36AA76ED395 ON doctor (user_id)');
        $this->addSql('ALTER TABLE doctor_info ADD doctor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE doctor_info ADD CONSTRAINT FK_8D080F3787F4FB17 FOREIGN KEY (doctor_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D080F3787F4FB17 ON doctor_info (doctor_id)');
        $this->addSql('UPDATE doctor_info AS t SET doctor_id = (SELECT d.id FROM doctor AS d WHERE d.id = t.id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE doctor_info DROP CONSTRAINT FK_8D080F3787F4FB17');
        $this->addSql('DROP INDEX UNIQ_8D080F3787F4FB17');
        $this->addSql('ALTER TABLE doctor_info DROP doctor_id');
        $this->addSql('ALTER TABLE doctor DROP CONSTRAINT FK_1FC0F36AA76ED395');
        $this->addSql('DROP INDEX UNIQ_1FC0F36AA76ED395');
        $this->addSql('ALTER TABLE doctor ADD surname VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE doctor ADD firstname VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE doctor ADD middlename VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE doctor DROP user_id');
        $this->addSql('COMMENT ON COLUMN doctor.surname IS \'Фамилия\'');
        $this->addSql('COMMENT ON COLUMN doctor.firstname IS \'Имя\'');
        $this->addSql('COMMENT ON COLUMN doctor.middlename IS \'Отчество\'');
    }
}
