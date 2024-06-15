<?php

declare(strict_types=1);

namespace App\Controller\Crud;

use App\Entity\Privilege;
use App\Entity\RefCitizenShip;
use App\Entity\RefOksm;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RefOksmCrudController extends AbstractCrudController {
    public static function getEntityFqcn(): string {
        return RefOksm::class;
    }
}
