<?php

declare(strict_types=1);

namespace App\Controller\Crud;

use App\Entity\Competencies;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class CompetenciesCrudController extends AbstractCrudController {
    public static function getEntityFqcn(): string {
        return Competencies::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Методы визуализации')
            ->setEntityLabelInSingular('Метод')
            ->setPageTitle(Crud::PAGE_INDEX, 'Список методов')
            ->setPageTitle(Crud::PAGE_NEW, 'Добавление метода')
            ->setPageTitle(Crud::PAGE_EDIT, 'Редактирование метода')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Просмотр информации о методе');
    }

    public function configureActions(Actions $actions): Actions {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::DELETE)
            ->disable(Action::BATCH_DELETE)
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $action) => $action->setLabel('Добавить метод'))
            ->setPermission(Action::EDIT, 'ROLE_ADMIN');
    }

    public function configureFields(string $pageName): iterable {
        yield NumberField::new('id')
            ->setLabel('ID');
        yield TextField::new('modality')
            ->setLabel('Наименование');
        yield TextField::new('contrast')
            ->setLabel('Контрастное усиление');
        yield NumberField::new('minimalCountPerShift')
            ->setLabel('Мин. кол-во исследований за смену, шт.')
            ->setFormTypeOptions([
                'attr' => [
                    'step' => '0.01',
                ],
            ]);
        yield NumberField::new('maxCountPerShift')
            ->setLabel('Макс. кол-во исследований за смену, шт.')
            ->setFormTypeOptions([
                'attr' => [
                    'step' => '0.01',
                ],
            ]);
        yield NumberField::new('minimalCoefficientPerShift')
            ->setLabel('Мин. кол-во УЕ за смену, шт.')
            ->setFormTypeOptions([
                'attr' => [
                    'step' => '0.01',
                ],
            ]);
        yield NumberField::new('maxCoefficientPerShift')
            ->setLabel('Макс. кол-во УЕ за смену (с округлением вниз до целого числа)')
            ->setFormTypeOptions([
                'attr' => [
                    'step' => '0.01',
                ],
            ]);
        yield NumberField::new('coefficient')
            ->setLabel('Кол-во УЕ в одном описании')
            ->setFormTypeOptions([
                'attr' => [
                    'step' => '0.01',
                ],
            ]);
    }
}
