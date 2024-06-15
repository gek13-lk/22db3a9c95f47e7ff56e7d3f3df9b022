<?php

namespace App\Twig;

use App\Repository\TempDoctorScheduleRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ScheduleExtension extends AbstractExtension {

    public function __construct(private TempDoctorScheduleRepository $repository) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('findSchedule', [$this, 'findSchedule']),
        ];
    }

    public function findSchedule(int $scheduleId, int $doctorId)
    {
        return $this->repository->findByTempSchedule($scheduleId, $doctorId);
    }
}