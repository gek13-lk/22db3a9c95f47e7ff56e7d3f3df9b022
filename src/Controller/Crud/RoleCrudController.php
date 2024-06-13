<?php

declare(strict_types=1);

namespace App\Controller\Crud;

use App\Entity\Privilege;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RoleCrudController extends AbstractCrudController {
    public static function getEntityFqcn(): string {
        return Role::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Роли')
            ->setEntityLabelInSingular('Роль')
            ->setPageTitle(Crud::PAGE_INDEX, 'Список ролей')
            ->setPageTitle(Crud::PAGE_NEW, 'Добавление роли')
            ->setPageTitle(Crud::PAGE_EDIT, 'Редактирование роли')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Просмотр роли')
            ->overrideTemplates([
                'layout' => 'admin/role_layout.html.twig',
            ])
            ;
    }

    public function configureActions(Actions $actions): Actions {
        return $actions
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $action) => $action->setLabel('Добавить роль'))
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::BATCH_DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::DETAIL, 'ROLE_ADMIN');
    }

    public function configureFields(string $pageName): iterable {
        yield TextField::new('name')
            ->setLabel('Наименование');
        yield TextField::new('code')
            ->setLabel('Системное наименование');

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

    private function getEntityManager(): EntityManagerInterface {
        return $this->container->get('doctrine')->getManager();
    }
}
