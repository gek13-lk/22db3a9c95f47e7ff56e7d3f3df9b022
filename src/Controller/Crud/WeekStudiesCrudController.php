<?php

declare(strict_types=1);

namespace App\Controller\Crud;

use App\Entity\Competencies;
use App\Entity\WeekStudies;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

final class WeekStudiesCrudController extends AbstractCrudController {
    public static function getEntityFqcn(): string {
        return WeekStudies::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('История исследований по неделям')
            ->setEntityLabelInSingular('Запись')
            ->setPageTitle(Crud::PAGE_INDEX, 'Список')
            ->setPageTitle(Crud::PAGE_NEW, 'Добавление записи')
            ->setPageTitle(Crud::PAGE_EDIT, 'Редактирование записи')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Просмотр данных об исследовании')
            ->setDefaultSort(['year' => 'DESC', 'weekNumber' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions {
        return $actions
            ->disable(Action::DELETE)
            ->disable(Action::BATCH_DELETE)
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $action) => $action->setLabel('Добавить новые данные'));
    }

    public function configureFields(string $pageName): iterable {
        $competencies = $this->getEntityManager()->getRepository(Competencies::class)->findAll();
        foreach ($competencies as $competency) {
                $choices[$competency->getId()] = $competency->getModality();
        }

        yield NumberField::new('year')
            ->setLabel('Год')
            ->setThousandsSeparator('');
        yield NumberField::new('weekNumber')
            ->setLabel('Номер недели');
        yield NumberField::new('count')
            ->setLabel('Количество проведенных исследований, шт.')
            ->setThousandsSeparator('');
        yield DateField::new('startOfWeek')
            ->setLabel('Дата начала недели')
            ->setFormat('dd.MM.yyyy');
        yield AssociationField::new('competency')
            ->setLabel('Метод визуализации')
            ->setFormTypeOption('choice_label', 'modality');
    }

    private function getEntityManager(): EntityManagerInterface {
        return $this->container->get('doctrine')->getManager();
    }
}
