<?php

declare(strict_types=1);

namespace App\Controller\Crud;

use App\Entity\Doctor;
use App\Entity\DoctorWorkSchedule;
use App\Entity\RefOksm;
use App\Entity\User;
use App\Voter\DoctorVoter;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DoctorCrudController extends AbstractCrudController {
    public static function getEntityFqcn(): string {
        return Doctor::class;
    }

    public function __construct(private UserPasswordHasherInterface $hasher) {
    }

    public function configureCrud(Crud $crud): Crud {
        return $crud
            ->setEntityLabelInPlural('Врачи')
            ->setEntityLabelInSingular('Врач')
            ->setEntityPermission(DoctorVoter::LIST)
            ->setPageTitle(Crud::PAGE_INDEX, 'Список врачей')
            ->setPageTitle(Crud::PAGE_NEW, 'Добавление врача')
            ->setPageTitle(Crud::PAGE_EDIT, 'Редактирование врача')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Просмотр врача')
            ->setFormOptions(['attr' => ['class' => 'w-100']]);
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

    public function configureFields(string $pageName): iterable {
        yield IntegerField::new('id')
            ->setLabel('Идентификатор')
            ->hideOnForm();
        yield TextField::new('fio')
            ->setLabel('ФИО')
            ->hideOnForm();
        yield TextField::new('info.snils')
            ->setLabel('СНИЛС')
            ->formatValue(fn($value) => !$value
                ? '---'
                : substr($value, 0, 3) . "-" .
                substr($value, 3, 3) . "-" .
                substr($value, 6, 3) . " " .
                substr($value, 9, 2))
            ->hideOnForm();


        yield FormField::addColumn(label: 'Учетная запись')
            ->onlyOnForms();
        yield TextField::new('user.username', 'Логин')
            ->onlyOnForms();
        yield TextField::new('user.plainPassword')
            ->setLabel("Новый пароль")
            ->setFormType(PasswordType::class)
            ->setFormTypeOption('empty_data', '')
            ->setRequired(false)
            ->setHelp('Оставьте поле пустым, если не хотите изменять текущий пароль!')
            ->onlyWhenUpdating();
        yield TextField::new('user.plainPassword')
            ->setLabel("Пароль")
            ->setFormType(PasswordType::class)
            ->setFormTypeOption('empty_data', '')
            ->setRequired(true)
            ->onlyWhenCreating();
        yield TextField::new('info.email', 'Адрес электронной почты')
            ->setRequired(true)
            ->onlyOnForms();


        yield FormField::addColumn(label: 'Персональная информация')
            ->onlyOnForms();
        yield TextField::new('info.lastName', 'Фамилия')
            ->setRequired(true)
            ->onlyOnForms();
        yield TextField::new('info.firstName', 'Имя')
            ->setRequired(true)
            ->onlyOnForms();
        yield TextField::new('info.patronymic', 'Отчество')
            ->setRequired(true)
            ->onlyOnForms();
        yield ChoiceField::new('info.gender', 'Пол')
            ->setRequired(true)
            ->setChoices([
                'мужской' => 1,
                'женский' => 2,
            ])->autocomplete()
            ->onlyOnForms();
        yield DateField::new('info.birthDate', 'Дата рождения')
            ->setRequired(true)
            ->onlyOnForms();
        yield TextField::new('info.snils', 'СНИЛС')
            ->setRequired(true)
            ->onlyOnForms();
        yield TextField::new('info.inn', 'ИНН')
            ->setRequired(true)
            ->onlyOnForms();
        yield TextField::new('info.phone', 'Номер телефона')
            ->setRequired(true)
            ->onlyOnForms();
        yield AssociationField::new('info.oksm', 'Гражданство')
            ->setRequired(true)
            ->setCrudController(RefOksmCrudController::class)
            ->setFormTypeOption('choice_label', 'name')
            ->setColumns('col-12')
            ->onlyOnForms();
        yield AssociationField::new('info.citizenShip', 'Категории гражданства')
            ->setRequired(true)
            ->setCrudController(RefCitizenShipCrudController::class)
            ->setFormTypeOption('choice_label', 'name')
            ->setColumns('col-12')
            ->onlyOnForms();

        yield FormField::addColumn(label: 'Информация о специализации')
            ->onlyOnForms();

        yield ChoiceField::new('mainCompetencies')
            ->setLabel('Модальность')
            ->setChoices([
                'Денситометрия' => 'Денситометрия',
                'КТ' => 'КТ',
                'МРТ' => 'МРТ',
                'РГ' => 'РГ',
                'ФЛГ' => 'ФЛГ',
                'ММГ' => 'ММГ',
            ])
            ->setColumns('col-12')
            ->allowMultipleChoices()
            ->renderAsBadges();
        yield ChoiceField::new('addonCompetencies')
            ->setLabel('Дополнительные модальности')
            ->setChoices([
                'Денситометрия' => 'Денситометрия',
                'КТ' => 'КТ',
                'МРТ' => 'МРТ',
                'РГ' => 'РГ',
                'ФЛГ' => 'ФЛГ',
                'ММГ' => 'ММГ',
            ])
            ->setColumns('col-12')
            ->allowMultipleChoices()
            ->renderAsBadges();
        yield NumberField::new('stavka', 'Ставка')
            ->setNumDecimals(2)
            ->setFormTypeOption('attr', ['min' => 0.25, 'step' => 0.25, 'max' => 1]);
        yield BooleanField::new('info.mr', 'Признак наличия работника в ФРМР')
            ->setFormTypeOption('row_attr', ['class' => 'custom-control custom-checkbox pl-1'])
            ->setFormTypeOption('attr', ['class' => 'custom-control-input'])
            ->setFormTypeOption('label_attr', ['class' => 'custom-control-label p-0 border-0', 'style' => 'height: unset;'])
            ->onlyOnForms();
        yield TextField::new('info.oid', 'OID работника')
            ->onlyOnForms();
        yield BooleanField::new('info.student', 'Признак обучающегося')
            ->setFormTypeOption('row_attr', ['class' => 'custom-control custom-checkbox pl-1'])
            ->setFormTypeOption('attr', ['class' => 'custom-control-input'])
            ->setFormTypeOption('label_attr', ['class' => 'custom-control-label p-0 border-0', 'style' => 'height: unset;'])
            ->onlyOnForms();
        yield BooleanField::new('info.isMedicalWorker', 'Признак медицинского работника')
            ->setFormTypeOption('row_attr', ['class' => 'custom-control custom-checkbox pl-1'])
            ->setFormTypeOption('attr', ['class' => 'custom-control-input'])
            ->setFormTypeOption('label_attr', ['class' => 'custom-control-label p-0 border-0', 'style' => 'height: unset;'])
            ->setHelp('работники, которые трудоустроены на медицинских должностях в организации, имеющей лицензию на медицинскую деятельность, которые в тоже время имеют действующую аккредитацию/сертификат специалиста по медицинским специальностям')
            ->onlyOnForms();
        yield BooleanField::new('info.isPharmWorker', 'Признак фармацевтического работника')
            ->setFormTypeOption('row_attr', ['class' => 'custom-control custom-checkbox pl-1'])
            ->setFormTypeOption('attr', ['class' => 'custom-control-input'])
            ->setFormTypeOption('label_attr', ['class' => 'custom-control-label p-0 border-0', 'style' => 'height: unset;'])
            ->setHelp('работники, которые трудоустроены на фармацевтических должностях в организации, имеющей лицензию на фармацевтическую деятельность, которые в тоже время имеют действующую аккредитацию/сертификат специалиста по фармацевтическим специальностям')
            ->onlyOnForms();

        yield FormField::addColumn(label: 'Режим работы')
            ->onlyOnForms();
        yield ChoiceField::new('workSchedule.type')
            ->setLabel('Тип смены')
            ->setChoices([
                DoctorWorkSchedule::TYPE_DAY => DoctorWorkSchedule::TYPE_DAY,
                DoctorWorkSchedule::TYPE_NIGHT => DoctorWorkSchedule::TYPE_NIGHT,
                DoctorWorkSchedule::TYPE_ONE_TO_THREE => DoctorWorkSchedule::TYPE_ONE_TO_THREE,
                DoctorWorkSchedule::TYPE_DAY_NIGHT => DoctorWorkSchedule::TYPE_DAY_NIGHT,
                DoctorWorkSchedule::TYPE_TWO_OFF => DoctorWorkSchedule::TYPE_TWO_OFF,
            ])
            ->setColumns('col-12')
            ->renderAsBadges()
            ->onlyOnForms();
        yield IntegerField::new('workSchedule.hoursPerShift', 'Количество часов за смену')
            ->setFormTypeOption('attr', ['min' => 1, 'max' => 24])
            ->onlyOnForms();
        yield IntegerField::new('workSchedule.shiftPerCycle', 'Смен за цикл')
            ->setFormTypeOption('attr', ['min' => 1, 'max' => 12])
            ->onlyOnForms();
        yield IntegerField::new('workSchedule.daysOff', 'Количество выходных дней за цикл')
            ->setFormTypeOption('attr', ['min' => 1, 'max' => 12])
            ->onlyOnForms();
        yield IntegerField::new('workSchedule.shiftStartTimeHour', 'Желаемое время начала смены (час)')
            ->setFormTypeOption('attr', ['min' => 1, 'max' => 24])
            ->onlyOnForms();
        yield IntegerField::new('workSchedule.shiftStartTimeMinutes', 'Желаемое время начала смены (Минуты)')
            ->setFormTypeOption('attr', ['min' => 1, 'max' => 60])
            ->onlyOnForms();
    }

    /**
     * @param Doctor $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void {
        $plainPassword = $entityInstance->getUser()->getPlainPassword();
        if (!empty($plainPassword)) {
            $password = $this->hasher->hashPassword($entityInstance->getUser(), $plainPassword);
            $entityInstance->getUser()->setPassword($password);
        }

        $entityInstance->getUser()->setRoles(['ROLE_DOCTOR']);
        $entityInstance->getUser()->setSurname($entityInstance->getInfo()->getLastName());
        $entityInstance->getUser()->setFirstname($entityInstance->getInfo()->getFirstName());
        $entityInstance->getUser()->setMiddlename($entityInstance->getInfo()->getPatronymic());
        $entityInstance->getUser()->setEmail($entityInstance->getInfo()->getEmail());

        $this->fillCompetencies($entityInstance);

        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @param Doctor $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void {
        $plainPassword = $entityInstance->getUser()->getPlainPassword();
        if (!empty($plainPassword)) {
            $password = $this->hasher->hashPassword($entityInstance->getUser(), $plainPassword);
            $entityInstance->getUser()->setPassword($password);
        }

        $entityInstance->getUser()->setSurname($entityInstance->getInfo()->getLastName());
        $entityInstance->getUser()->setFirstname($entityInstance->getInfo()->getFirstName());
        $entityInstance->getUser()->setMiddlename($entityInstance->getInfo()->getPatronymic());
        $entityInstance->getUser()->setEmail($entityInstance->getInfo()->getEmail());

        $this->fillCompetencies($entityInstance);

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function fillCompetencies(Doctor $entityInstance): void {
        if (\in_array('КТ', $entityInstance->getMainCompetencies())) {
            $entityInstance->setMainCompetencies(array_unique(array_merge($entityInstance->getMainCompetencies(),
                ['КТ с КУ 2 и более зон', 'КТ с КУ 1 зона'])));
        }

        if (\in_array('МРТ', $entityInstance->getMainCompetencies())) {
            $entityInstance->setMainCompetencies(array_unique(array_merge($entityInstance->getMainCompetencies(),
                ['МРТ с КУ 2 и более зон', 'МРТ с КУ 1 зона'])));
        }

        if (\in_array('КТ', $entityInstance->getAddonCompetencies())) {
            $entityInstance->setAddonCompetencies(array_unique(array_merge($entityInstance->getAddonCompetencies(),
                ['КТ с КУ 2 и более зон', 'КТ с КУ 1 зона'])));
        }

        if (\in_array('МРТ', $entityInstance->getAddonCompetencies())) {
            $entityInstance->setAddonCompetencies(array_unique(array_merge($entityInstance->getAddonCompetencies(),
                ['МРТ с КУ 2 и более зон', 'МРТ с КУ 1 зона'])));
        }
    }
}
