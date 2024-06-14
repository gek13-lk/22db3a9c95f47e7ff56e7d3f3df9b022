<?php

declare(strict_types=1);

namespace App\Controller\Crud;

use App\Entity\Privilege;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserCrudController extends AbstractCrudController {

    public static function getEntityFqcn(): string {
        return User::class;
    }

    public function __construct(private UserPasswordHasherInterface $hasher) {
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Пользователи')
            ->setEntityLabelInSingular('Пользователь')
            ->setPageTitle(Crud::PAGE_INDEX, 'Список пользователей')
            ->setPageTitle(Crud::PAGE_NEW, 'Добавление пользователя')
            ->setPageTitle(Crud::PAGE_EDIT, 'Редактирование пользователя')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Просмотр пользователя')
            ->overrideTemplates([
                'layout' => 'admin/user_layout.html.twig',
                'crud/detail' => 'admin/custom_detail.html.twig',
            ])
            ;
    }

    public function configureActions(Actions $actions): Actions {
        return $actions
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $action) => $action->setLabel('Добавить пользователя'))
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::BATCH_DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::DETAIL, 'ROLE_ADMIN');
    }

    public function configureFields(string $pageName): iterable {
        yield TextField::new('username', 'Имя пользователя');

        yield TextField::new('plainPassword')
            ->setLabel("Новый пароль")
            ->setFormType(PasswordType::class)
            ->setFormTypeOption('empty_data', '')
            ->setRequired(false)
            ->setHelp('Оставьте поле пустым, если не хотите изменять текущий пароль!')
            ->onlyWhenUpdating();

        yield TextField::new('plainPassword')
            ->setLabel("Пароль")
            ->setFormType(PasswordType::class)
            ->setFormTypeOption('empty_data', '')
            ->setRequired(true)
            ->onlyWhenCreating();

        foreach ($this->getEntityManager()->getRepository(Role::class)->findAll() as $role) {
            $roles[$role->getName()] = $role->getCode();
        }

        yield ChoiceField::new('roles')
            ->setLabel('Роли')
            ->setRequired(false)
            ->setChoices($roles ?? [])
            ->allowMultipleChoices()
            ->autocomplete()
            ->renderAsBadges()
            ->setSortable(false);

        foreach ($this->getEntityManager()->getRepository(Privilege::class)->findAll() as $privilege) {
            $privileges[$privilege->getName()] = $privilege->getCode();
        }

        yield ChoiceField::new('privileges')
            ->setLabel('Привилегии')
            ->setRequired(false)
            ->setChoices($privileges ?? [])
            ->allowMultipleChoices()
            ->autocomplete()
            ->renderAsBadges()
            ->setSortable(false);
    }

    /**
     * @param User $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void {
        $plainPassword = $entityInstance->getPlainPassword();
        if (!empty($plainPassword)) {
            $password = $this->hasher->hashPassword($entityInstance, $plainPassword);
            $entityInstance->setPassword($password);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @param User $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void {
        $plainPassword = $entityInstance->getPlainPassword();
        if (!empty($plainPassword)) {
            $password = $this->hasher->hashPassword($entityInstance, $plainPassword);
            $entityInstance->setPassword($password);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->container->get('doctrine')->getManager();
    }
}
