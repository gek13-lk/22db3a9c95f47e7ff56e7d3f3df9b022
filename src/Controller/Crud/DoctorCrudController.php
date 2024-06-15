<?php

declare(strict_types=1);

namespace App\Controller\Crud;

use App\Entity\Doctor;
use App\Voter\DoctorVoter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DoctorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Doctor::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Врачи')
            ->setEntityLabelInSingular('Врач')
            ->setEntityPermission(DoctorVoter::LIST)
            ->setPageTitle(Crud::PAGE_INDEX, 'Список врачей')
            ->setPageTitle(Crud::PAGE_NEW, 'Добавление врача')
            ->setPageTitle(Crud::PAGE_EDIT, 'Редактирование врача')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Просмотр врача');
    }

    public function configureActions(Actions $actions): Actions {
        return $actions
            ->setPermission(Action::NEW, DoctorVoter::ADD)
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $action) => $action->setLabel('Добавить врача'))
            ->setPermission(Action::EDIT, DoctorVoter::EDIT)
            ->setPermission(Action::DELETE, DoctorVoter::REMOVE)
            ->setPermission(Action::BATCH_DELETE, DoctorVoter::REMOVE)
            ->setPermission(Action::DETAIL, DoctorVoter::SHOW);
    }
}
