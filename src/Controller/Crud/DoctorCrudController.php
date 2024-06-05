<?php

namespace App\Controller\Crud;

use App\Entity\Doctor;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DoctorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Doctor::class;
    }
}
