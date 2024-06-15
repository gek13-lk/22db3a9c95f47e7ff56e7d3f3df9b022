<?php

declare(strict_types=1);

namespace App\Modules\Algorithm;

use App\Entity\TempDoctorSchedule;
use App\Entity\TempSchedule;
use Doctrine\ORM\EntityManagerInterface;

class RecommendationService
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function getRecommendation(TempSchedule $tempSchedule): array
    {
        $doctorSchedules = $this->em->getRepository(TempDoctorSchedule::class)->findByTempSchedule($tempSchedule);

        $scheduleData = [];
        /** @var TempDoctorSchedule $schedule */
        foreach ($doctorSchedules as $schedule) {
            $scheduleData[] = [
                "id" => $schedule->getId(),
                'doctor' => $schedule->getDoctor()->getId(),
                "date" => $schedule->getDate()->format('d.m.Y'),
                "doctorWorkSchedule" => $schedule->getDoctor()->getWorkSchedule()->getType(),
                "doctorWorkTimeStart" => $schedule->getWorkTimeStart() ?? null,
                "doctorWorkTimeEnd" => $schedule->getWorkTimeEnd() ?? null,
                "doctorOffMinutes" => $schedule->getOffMinutes(),
                "doctorWorkHours" => $schedule->getWorkHours(),
                "doctorModality" => $schedule->getDoctor()->getCompetency(),
                "doctorRate" => $schedule->getDoctor()->getStavka(),
                "studyModality" => $schedule->getTempScheduleWeekStudies()->getWeekStudies()->getCompetency()->getModality(),
                "studyCount" => $schedule->getTempScheduleWeekStudies()->getWeekStudies()->getCount(),
            ];
        }

        $recommendations = $this->generateRecommendations($scheduleData);

        return $recommendations;
    }

    public function generateRecommendations($scheduleData)
    {
        $recommendations = [];
        $doctorWorkloads = [];
        $doctorDaysWorked = [];
        $doctorWorkTimes = [];
        $doctorOffMinutes = [];
        $doctorShifts = [];
        $doctorModalityAssignments = [];
        $shiftDoctors = [];
        $doctorWeeklyHours = [];
        $doctorMonthlyHours = [];
        $shiftTypes = [
            'Дневные смены',
            'Ночные смены',
            'Сутки через трое',
            'День-ночь',
            'Два выходных'
        ];

        foreach ($scheduleData as $data) {
            $doctorId = $data['doctor'];
            $date = $data['date'];
            $modality = $data['studyModality'];
            $doctorModality = $data['doctorModality'];
            $studyCount = $data['studyCount'];
            $workSchedule = $data['doctorWorkSchedule'];
            $workTimeStart = $data['doctorWorkTimeStart'];
            $workTimeEnd = $data['doctorWorkTimeEnd'];
            $offMinutes = $data['doctorOffMinutes'];
            $rate = $data['doctorRate']; // ставка врача

            if (!isset($doctorWorkloads[$doctorId])) {
                $doctorWorkloads[$doctorId] = 0;
            }
            $doctorWorkloads[$doctorId] += $studyCount;

            if (!isset($doctorDaysWorked[$doctorId])) {
                $doctorDaysWorked[$doctorId] = [];
            }
            $day = date('w', strtotime($date)); // Получаем день недели (0 - воскресенье, 6 - суббота)
            $doctorDaysWorked[$doctorId][$day] = true;

            if (!isset($doctorWorkTimes[$doctorId])) {
                $doctorWorkTimes[$doctorId] = [];
            }
            $doctorWorkTimes[$doctorId][] = [$workTimeStart, $workTimeEnd];

            if (!isset($doctorOffMinutes[$doctorId])) {
                $doctorOffMinutes[$doctorId] = 0;
            }
            $doctorOffMinutes[$doctorId] += $offMinutes;

            if (!isset($doctorShifts[$doctorId])) {
                $doctorShifts[$doctorId] = [];
            }
            $doctorShifts[$doctorId][] = $workSchedule;

            if (!isset($doctorModalityAssignments[$doctorId])) {
                $doctorModalityAssignments[$doctorId] = [];
            }
            $doctorModalityAssignments[$doctorId]['studyModality'] = $modality;
            $doctorModalityAssignments[$doctorId]['doctorModality'] = $doctorModality;

            // Анализ количества врачей в смене
            if (!isset($shiftDoctors[$date])) {
                $shiftDoctors[$date] = [
                    'Дневные смены' => [],
                    'Ночные смены' => [],
                    'Сутки через трое' => [],
                    'День-ночь' => [],
                    'Два выходных' => [],
                ];
            }
            $shiftDoctors[$date][$workSchedule][] = $doctorId;

            // Учет недельных и месячных часов работы врача
            $weekNumber = date('W', strtotime($date)); // Получаем номер недели
            $month = date('m', strtotime($date)); // Получаем номер месяца

            if (!isset($doctorWeeklyHours[$doctorId])) {
                $doctorWeeklyHours[$doctorId] = [];
            }
            if (!isset($doctorWeeklyHours[$doctorId][$weekNumber])) {
                $doctorWeeklyHours[$doctorId]['hours'][$weekNumber] = 0;
            }

            $workDuration = ($workTimeStart && $workTimeEnd) ? (strtotime($workTimeEnd) - strtotime($workTimeStart)) / 3600 : 0;
            $doctorWeeklyHours[$doctorId]['hours'][$weekNumber] += $workDuration;
            $doctorWeeklyHours[$doctorId]['rate'] = $rate;

            if (!isset($doctorMonthlyHours[$doctorId])) {
                $doctorMonthlyHours[$doctorId] = [];
            }
            if (!isset($doctorMonthlyHours[$doctorId][$month])) {
                $doctorMonthlyHours[$doctorId]['hours'][$month] = 0;
            }
            $doctorMonthlyHours[$doctorId]['hours'][$month] += $workDuration;
            $doctorMonthlyHours[$doctorId]['rate'] = $rate;
        }


        try {
            // Учет перегрузки врачей
            foreach ($doctorWorkloads as $doctorId => $workload) {
                if ($workload > 5) {
                    $recommendations[] = "Доктор $doctorId перегружен. Рассмотрите возможность перераспределения задач.";
                }
            }

            // Проверка равномерности распределения задач
            $avgWorkload = array_sum($doctorWorkloads) / count($doctorWorkloads);
            foreach ($doctorWorkloads as $doctorId => $workload) {
                if (abs($workload - $avgWorkload) > 2) {
                    $recommendations[] = "Нагрузка на доктора $doctorId отклоняется от средней. Попробуйте сбалансировать задачи.";
                }
            }

            // Минимизация перерывов
            foreach ($doctorDaysWorked as $doctorId => $daysWorked) {
                $days = array_keys($daysWorked);
                for ($i = 1; $i < count($days); $i++) {
                    if ($days[$i] - $days[$i - 1] > 1) {
                        $recommendations[] = "Доктор $doctorId имеет большие перерывы между рабочими днями. Сократите перерывы.";
                    }
                }
            }

            // Минимизация выходных дней подряд
            foreach ($doctorDaysWorked as $doctorId => $daysWorked) {
                $days = array_keys($daysWorked);
                $maxConsecutiveDaysOff = 0;
                $currentConsecutiveDaysOff = 0;

                for ($day = 0; $day < 7; $day++) {
                    if (!isset($daysWorked[$day])) {
                        $currentConsecutiveDaysOff++;
                    } else {
                        if ($currentConsecutiveDaysOff > $maxConsecutiveDaysOff) {
                            $maxConsecutiveDaysOff = $currentConsecutiveDaysOff;
                        }
                        $currentConsecutiveDaysOff = 0;
                    }
                }
                if ($currentConsecutiveDaysOff > $maxConsecutiveDaysOff) {
                    $maxConsecutiveDaysOff = $currentConsecutiveDaysOff;
                }

                if ($maxConsecutiveDaysOff > 2) {
                    $recommendations[] = "Доктор $doctorId имеет более 2 выходных дней подряд. Попробуйте их сократить.";
                }
            }

            // Учет времени работы и перерывов
            foreach ($doctorWorkTimes as $doctorId => $workTimes) {
                foreach ($workTimes as $workTime) {
                    list($start, $end) = $workTime;
                    if ($start !== null && $end !== null) {
                        $workDuration = (strtotime($end) - strtotime($start)) / 60; // Время работы в минутах
                        if ($workDuration > 480) { // если работа длится более 8 часов
                            $recommendations[] = "Доктор $doctorId работает более 8 часов подряд. Попробуйте сократить длительность смены.";
                        }
                    }
                }
            }

            // Учет минут перерыва
            foreach ($doctorOffMinutes as $doctorId => $offMinutes) {
                if ($offMinutes < 30) { // если перерывов менее 30 минут
                    $recommendations[] = "Доктор $doctorId имеет недостаточно времени на перерывы. Увеличьте количество или длительность перерывов.";
                }
            }

            // Учет смен (день-ночь)
            foreach ($doctorShifts as $doctorId => $shifts) {
                $shiftCounts = array_fill_keys($shiftTypes, 0);
                foreach ($shifts as $shift) {
                    if (isset($shiftCounts[$shift])) {
                        $shiftCounts[$shift]++;
                    }
                }
                foreach ($shiftCounts as $shiftType => $count) {
                    if ($count > 3) {
                        $recommendations[] = "Доктор $doctorId имеет слишком много смен типа '$shiftType'. Убедитесь в чередовании смен.";
                    }
                }
            }

            // Учет соответствия компетенций и модальностей исследований
            foreach ($doctorModalityAssignments as $doctorId => $modality) {
                if (!in_array($modality['studyModality'], $modality['doctorModality'])) {
                    $recommendations[] = "Доктор $doctorId назначен на исследование" . $modality['studyModality'] . ", которое не соответствует его компетенциям.";
                }
            }

            // Анализ нехватки или чрезмерного количества врачей в смене
            foreach ($shiftDoctors as $date => $shifts) {
                foreach ($shifts as $shiftType => $doctors) {
                    if (count($doctors) < 2) {
                        $recommendations[] = "В смене '$shiftType' на дату $date недостаточно врачей (меньше 2). Рассмотрите возможность добавления врачей.";
                    }
                    if (count($doctors) > 5) {
                        $recommendations[] = "В смене '$shiftType' на дату $date слишком много врачей (более 5). Рассмотрите возможность перераспределения.";
                    }
                }
            }

            // Учет недельных и месячных часов работы врача
            foreach ($doctorWeeklyHours as $doctorId => $weeklyHours) {
                foreach ($weeklyHours['hours'] as $weekNumber => $hours) {
                    $expectedWeeklyHours = 40 * $weeklyHours['rate'];
                    if ($hours > $expectedWeeklyHours) {
                        $recommendations[] = "Доктор $doctorId работает более $expectedWeeklyHours часов в неделю. Попробуйте сократить часы работы.";
                    }
                }
            }
            foreach ($doctorMonthlyHours as $doctorId => $monthlyHours) {
                foreach ($monthlyHours['hours'] as $month => $hours) {
                    if ($hours > 155 * $weeklyHours['rate']) {
                        $recommendations[] = "Доктор $doctorId работает более " . (155 * $weeklyHours['rate']) . " часов в месяц. Попробуйте сократить часы работы.";
                    }
                }
            }
        } catch (\Throwable) {

        }

        return $recommendations;
    }
}
