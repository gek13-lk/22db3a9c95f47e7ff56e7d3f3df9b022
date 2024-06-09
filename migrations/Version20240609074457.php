<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240609074457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE temp_doctor_schedule (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, date DATE NOT NULL, start_work_time VARCHAR(255) NOT NULL, end_work_time VARCHAR(255) NOT NULL, doctor_id INT DEFAULT NULL, temp_schedule_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C8843AD687F4FB17 ON temp_doctor_schedule (doctor_id)');
        $this->addSql('CREATE INDEX IDX_C8843AD696E57A11 ON temp_doctor_schedule (temp_schedule_id)');
        $this->addSql('CREATE TABLE temp_schedule (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE temp_schedule_week_studies (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, empty INT NOT NULL, week_studies_id INT DEFAULT NULL, temp_schedule_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_16DFB6AC56715103 ON temp_schedule_week_studies (week_studies_id)');
        $this->addSql('CREATE INDEX IDX_16DFB6AC96E57A11 ON temp_schedule_week_studies (temp_schedule_id)');
        $this->addSql('ALTER TABLE temp_doctor_schedule ADD CONSTRAINT FK_C8843AD687F4FB17 FOREIGN KEY (doctor_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE temp_doctor_schedule ADD CONSTRAINT FK_C8843AD696E57A11 FOREIGN KEY (temp_schedule_id) REFERENCES temp_schedule_week_studies (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE temp_schedule_week_studies ADD CONSTRAINT FK_16DFB6AC56715103 FOREIGN KEY (week_studies_id) REFERENCES week_studies (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE temp_schedule_week_studies ADD CONSTRAINT FK_16DFB6AC96E57A11 FOREIGN KEY (temp_schedule_id) REFERENCES temp_schedule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE temp_doctor_schedule DROP CONSTRAINT FK_C8843AD687F4FB17');
        $this->addSql('ALTER TABLE temp_doctor_schedule DROP CONSTRAINT FK_C8843AD696E57A11');
        $this->addSql('ALTER TABLE temp_schedule_week_studies DROP CONSTRAINT FK_16DFB6AC56715103');
        $this->addSql('ALTER TABLE temp_schedule_week_studies DROP CONSTRAINT FK_16DFB6AC96E57A11');
        $this->addSql('DROP TABLE temp_doctor_schedule');
        $this->addSql('DROP TABLE temp_schedule');
        $this->addSql('DROP TABLE temp_schedule_week_studies');
    }
}
