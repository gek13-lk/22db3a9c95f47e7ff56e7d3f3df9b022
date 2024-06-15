<?php


namespace App\Modules\Algorithm;

use App\Entity\Doctor;
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
    private const MAX_HOUR_WEEK = 36;
    private const MAX_HOUR_MONTH = 155;

    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function setTimeByTempSchedule(TempSchedule $tempSchedule): void
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

            $endTime = (clone $startTime)->modify('+ ' . $doctorWorkSchedule->getHoursPerShift() . ' hours')->modify(' +' . (self::START_WORK_MINUTES + $offTime) . ' minutes');
            $workHours = (clone $endTime)->modify('- ' . $offTime . ' minutes')->diff($startTime);

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

    private function getOstNorms(Doctor $doctor, array $doctorsStat, \DateTime $currentDay): array
    {
        $startMonth = (clone $currentDay)->modify('first day of this month 00:00:00');
        $startWeek = (clone $currentDay)->modify('monday this week 00:00:00');
        $currentMonthHours = 0;
        $currentWeekHours = 0;

        if (isset($doctorsStat[$doctor->getId()]) && isset($doctorsStat[$doctor->getId()]['days'])) {
            foreach ($doctorsStat[$doctor->getId()]['days'] as $dayString => $dayStat) {

                $day = \DateTime::createFromFormat('Y-m-d', $dayString);

                $startMonthInStat = (clone $day)->modify('first day of this month 00:00:00');
                $startWeekInStat = (clone $day)->modify('monday this week 00:00:00');
                if ($startMonthInStat != $startMonth) {
                    continue;
                }

                $currentMonthHours+= $dayStat['hours'];

                if ($startWeekInStat != $startWeek) {
                    continue;
                }

                $currentWeekHours+= $dayStat['hours'];
            }
        }

        return [
                'month' => self::MAX_HOUR_MONTH - $currentMonthHours,
                'week' => self::MAX_HOUR_WEEK - $currentWeekHours,
            ];
    }

    /**
     * Получаем время работы по врачу, если это возможно, не нарушая нормы
     */
    public function getTimeByDoctor(Doctor $doctor, \DateTime $currentDay, array $doctorStat): array|false
    {
        $lastShiftType = null;
        $doctorWorkSchedule = $doctor->getWorkSchedule();
        $startTime = (clone $currentDay)->setTime(self::START_WORK_HOUR, self::START_WORK_MINUTES);
        $offTime = self::DEFAULT_OFF_TIME;
        switch ($doctorWorkSchedule->getType()) {
            case 'Сутки через трое':
                //$offTime = 60;
                break;
            case 'Два выходных':
                break;
            case 'Ночные смены':
                $startTime = (clone $currentDay)->setTime(self::START_NIGHT_WORK_HOUR, self::START_WORK_MINUTES);
                break;
            case 'Дневные смены':
                break;
            case 'День-ночь':
                if (isset($doctorStat[$doctor->getId()]['lastShiftType'])) {
                    if ($doctorStat[$doctor->getId()]['lastShiftType'] == 'День') {
                        $startTime = (clone $currentDay)->setTime(self::START_NIGHT_WORK_HOUR, self::START_WORK_MINUTES);
                        $lastShiftType = 'Ночь';
                    } else {
                        $lastShiftType = 'День';
                    }
                } else {
                    if (rand(0, 1) == 1) {
                        $startTime = (clone $currentDay)->setTime(self::START_NIGHT_WORK_HOUR, self::START_WORK_MINUTES);
                        $lastShiftType = 'Ночь';
                    } else {
                        $lastShiftType = 'День';
                    }
                }

                break;
        }

        $doctorWorkHoursCount = $doctorWorkSchedule->getHoursPerShift() * $doctor->getStavka();

        if ($doctorWorkHoursCount >= 11) {
            $offTime = 60;
        }

        $doctorWorkMinutes = $doctorWorkHoursCount * 60;
        $endTime = (clone $startTime)->modify(' +' . (self::START_WORK_MINUTES + $offTime + $doctorWorkMinutes) . ' minutes');
        $workHours = (clone $endTime)->modify('- ' . $offTime . ' minutes')->diff($startTime);

        if ($workHours->days) {
            $workHours = 24 + $workHours->h;
        } else {
            $workHours = $workHours->h;
        }

        $res = $this->getOstNorms($doctor, $doctorStat, $currentDay);

        $monthHoursOst = $res['month'];
        $weekHoursOst = $res['week'];

        if ($monthHoursOst < $workHours) {
            $diff = $workHours - $monthHoursOst;

            if ($diff <= 0) {
                return false;
            }

            $endTime->modify('- ' . $diff . ' hours');
            $workHours = $monthHoursOst;
        }

        if ($weekHoursOst < $workHours) {
            $diff = $workHours - $weekHoursOst;

            if ($diff <= 0) {
                return false;
            }

            $endTime->modify('- ' . $diff . ' hours');
            $workHours = $weekHoursOst;
        }

        return [
            'hours' => $workHours,
            'start' => $startTime,
            'end' => $endTime,
            'off' => $offTime,
            'lastShiftType' => $lastShiftType,
        ];
    }
}
