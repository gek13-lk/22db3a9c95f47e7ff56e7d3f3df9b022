<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240528150027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE doctor (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, surname VARCHAR(255) DEFAULT NULL, firstname VARCHAR(255) DEFAULT NULL, middlename VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN doctor.surname IS \'Фамилия\'');
        $this->addSql('COMMENT ON COLUMN doctor.firstname IS \'Имя\'');
        $this->addSql('COMMENT ON COLUMN doctor.middlename IS \'Отчество\'');
        $this->addSql('CREATE TABLE doctor_doctor_speciality (doctor_id INT NOT NULL, doctor_speciality_id INT NOT NULL, PRIMARY KEY(doctor_id, doctor_speciality_id))');
        $this->addSql('CREATE INDEX IDX_12E02BE387F4FB17 ON doctor_doctor_speciality (doctor_id)');
        $this->addSql('CREATE INDEX IDX_12E02BE31EC00B ON doctor_doctor_speciality (doctor_speciality_id)');
        $this->addSql('CREATE TABLE doctor_speciality (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, name VARCHAR(255) DEFAULT NULL, code VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN doctor_speciality.name IS \'Наименование специальности\'');
        $this->addSql('COMMENT ON COLUMN doctor_speciality.code IS \'Код специальности\'');
        $this->addSql('ALTER TABLE doctor_doctor_speciality ADD CONSTRAINT FK_12E02BE387F4FB17 FOREIGN KEY (doctor_id) REFERENCES doctor (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE doctor_doctor_speciality ADD CONSTRAINT FK_12E02BE31EC00B FOREIGN KEY (doctor_speciality_id) REFERENCES doctor_speciality (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE doctor_doctor_speciality DROP CONSTRAINT FK_12E02BE387F4FB17');
        $this->addSql('ALTER TABLE doctor_doctor_speciality DROP CONSTRAINT FK_12E02BE31EC00B');
        $this->addSql('DROP TABLE doctor');
        $this->addSql('DROP TABLE doctor_doctor_speciality');
        $this->addSql('DROP TABLE doctor_speciality');
    }
}
