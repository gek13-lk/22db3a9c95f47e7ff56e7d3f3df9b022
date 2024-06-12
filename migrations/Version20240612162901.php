<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240612162901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление таблицы логирования обучения моделей прогозирования';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE model_training_logs (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_success BOOLEAN NOT NULL, user_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_52DBDBF5A76ED395 ON model_training_logs (user_id)');
        $this->addSql('ALTER TABLE model_training_logs ADD CONSTRAINT FK_52DBDBF5A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE model_training_logs DROP CONSTRAINT FK_52DBDBF5A76ED395');
        $this->addSql('DROP TABLE model_training_logs');
    }
}
