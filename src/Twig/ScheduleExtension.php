<?php

namespace App\Twig;

use App\Controller\SecurityController;
use App\Entity\Role;
use App\Repository\RoleRepository;
use App\Repository\TempDoctorScheduleRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ScheduleExtension extends AbstractExtension {

    public function __construct(private TempDoctorScheduleRepository $repository, private Security $secrity, private RoleRepository $roleRepository) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('findSchedule', [$this, 'findSchedule']),
            new TwigFunction('getRoles', [$this, 'getRoles']),
        ];
    }

    public function findSchedule(int $scheduleId, int $doctorId)
    {
        return $this->repository->findByTempScheduleAndDoctor($scheduleId, $doctorId);
    }

    public function getRoles()
    {
        return array_map(fn(Role $role) => $role->getName(), $this->roleRepository->findBy(['code' => $this->secrity->getUser()?->getRoles()]));
    }
}