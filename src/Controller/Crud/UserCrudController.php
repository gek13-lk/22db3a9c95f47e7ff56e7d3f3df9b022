<?php

namespace App\Controller\Crud;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Choice;

final class UserCrudController extends AbstractCrudController {

    public static function getEntityFqcn(): string {
        return User::class;
    }

    public function __construct(private UserPasswordHasherInterface $hasher) {
    }

    public function configureFields(string $pageName): iterable {
        yield TextField::new('username', 'Имя пользователя');

        yield TextField::new('plainPassword')
            ->setLabel("Новый пароль")
            ->setFormType(PasswordType::class)
            ->setFormTypeOption('empty_data', '')
            ->setRequired(false)
            ->setHelp('Оставьте поле пустым, если не хотите изменять текущий пароль пароль!')
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
            ->setChoices($roles ?? [])
            ->allowMultipleChoices()
            ->autocomplete();
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
