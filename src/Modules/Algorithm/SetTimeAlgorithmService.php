<?php

declare(strict_types=1);

namespace App\Modules\Algorithm;

use App\Entity\TempDoctorSchedule;
use App\Entity\TempSchedule;
use Doctrine\ORM\EntityManagerInterface;

class SetTimeAlgorithmService
{
    private const START_WORK_HOUR = 8;
    private const START_WORK_MINUTES = 0;
    private const START_NIGHT_WORK_HOUR = 20;
    private const START_NIGHT_WORK_MINUTES = 0;
    private const DEFAULT_OFF_TIME = 30;

    public function __construct(private readonly EntityManagerInterface $em) {}

    public function setTime(TempSchedule $tempSchedule): void
    {
        $doctorSchedules = $this->em->getRepository(TempDoctorSchedule::class)->findByTempSchedule($tempSchedule);

        /** @var TempDoctorSchedule $doctorSchedule */
        foreach ($doctorSchedules as $doctorSchedule) {
            $doctorWorkSchedule = $doctorSchedule->getDoctor()->getWorkSchedule();
            $currentDay = $doctorSchedule->getDate();
            $startTime = $currentDay->setTime(self::START_WORK_HOUR, self::START_WORK_MINUTES);
            $endTime = null;
            $offTime = self::DEFAULT_OFF_TIME;

            switch ($doctorWorkSchedule->getType()) {
                case 'Сутки через трое':
                    break;
                case 'Два выходных':
                    break;
                case 'Ночные смены':
                    $startTime = $currentDay->setTime(self::START_NIGHT_WORK_HOUR, self::START_WORK_MINUTES);
                    break;
                case 'Дневные смены':
                    break;
                case 'День-ночь':
                    if (rand(0, 1) == 1) {
                        $startTime = $currentDay->setTime(self::START_NIGHT_WORK_HOUR, self::START_WORK_MINUTES);
                    }

                    break;
            }

            $endTime = (clone $startTime)->modify('+ '.$doctorWorkSchedule->getHoursPerShift().' hours')->modify(' +'. (self::START_WORK_MINUTES + $offTime). ' minutes');
            $workHours = (clone $endTime)->modify('- '.$offTime.' minutes')->diff($startTime);

            if ($workHours->days) {
                $workHours = 24 + $workHours->h;
            } else {
                $workHours = $workHours->h;
            }

            $doctorSchedule->setOffMinutes($offTime);
            $doctorSchedule->setWorkTimeStart($startTime);
            $doctorSchedule->setWorkTimeEnd($endTime);
            $doctorSchedule->setWorkHours($workHours);
        }

        $this->em->flush();
    }
}
