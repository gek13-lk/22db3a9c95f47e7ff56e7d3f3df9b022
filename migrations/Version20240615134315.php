<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Doctrine\Migrations\HasherInterface;
use App\Entity\User;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240615134315 extends AbstractMigration implements HasherInterface
{
    private ?PasswordHasherFactoryInterface $hasher = null;

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO "user" (username, roles, password, privileges, email, surname, firstname, middlename) 
(SELECT CONCAT(\'doctor\', id), \'["ROLE_DOCTOR"]\', :password, \'[]\', email, last_name, first_name, patronymic FROM doctor_info)', [
            'password' => $this->getHasher()->getPasswordHasher(new User())->hash('password')
        ]);
        $this->addSql('UPDATE doctor AS d SET user_id = (SELECT u.id FROM "user" AS u JOIN doctor_info AS di ON di.email = u.email WHERE di.doctor_id = d.id) WHERE user_id IS NULL');
    }

    public function down(Schema $schema): void
    {
    }

    public function getHasher(): PasswordHasherFactoryInterface {
        return $this->hasher ?? throw new \LogicException();
    }

    public function setHasher(PasswordHasherFactoryInterface $hasher): void {
        $this->hasher = $hasher;
    }
}
