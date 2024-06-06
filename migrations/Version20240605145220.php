<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Doctrine\Migrations\HasherInterface;
use App\Entity\User;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final class Version20240605145220 extends AbstractMigration implements HasherInterface
{
    private ?PasswordHasherFactoryInterface $hasher = null;

    public function getDescription(): string
    {
        return 'Добавление пользователей и ролей';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO role (code, name, privileges) VALUES (\'ROLE_ADMIN\', \'Администратор\', \'[]\')');
        $this->addSql('INSERT INTO role (code, name, privileges) VALUES (\'ROLE_MANAGER\', \'Руководитель референс-центра\', \'[]\')');
        $this->addSql('INSERT INTO role (code, name, privileges) VALUES (\'ROLE_HR\', \'Сотрудник кадровой службы\', \'[]\')');
        $this->addSql('INSERT INTO role (code, name, privileges) VALUES (\'ROLE_DOCTOR\', \'Врач\', \'[]\')');

        $this->addSql('INSERT INTO "user" (username, roles, password) VALUES (\'admin\', \'["ROLE_ADMIN"]\', :password);', ['password' => $this->getHasher()->getPasswordHasher(new User())->hash('admin')]);
        $this->addSql('INSERT INTO "user" (username, roles, password) VALUES (\'manager\', \'["ROLE_MANAGER"]\', :password);', ['password' => $this->getHasher()->getPasswordHasher(new User())->hash('manager')]);
        $this->addSql('INSERT INTO "user" (username, roles, password) VALUES (\'hr\', \'["ROLE_HR"]\', :password);', ['password' => $this->getHasher()->getPasswordHasher(new User())->hash('hr')]);
        $this->addSql('INSERT INTO "user" (username, roles, password) VALUES (\'doctor\', \'["ROLE_DOCTOR"]\', :password);', ['password' => $this->getHasher()->getPasswordHasher(new User())->hash('doctor')]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM role WHERE code IN (\'ROLE_ADMIN\', \'ROLE_MANAGER\', \'ROLE_HR\', \'ROLE_DOCTOR\')');
        $this->addSql('DELETE FROM "user" WHERE username IN (\'admin\', \'manager\', \'hr\', \'doctor\')');
    }

    public function getHasher(): PasswordHasherFactoryInterface {
        return $this->hasher ?? throw new \LogicException();
    }

    public function setHasher(PasswordHasherFactoryInterface $hasher): void {
        $this->hasher = $hasher;
    }
}
