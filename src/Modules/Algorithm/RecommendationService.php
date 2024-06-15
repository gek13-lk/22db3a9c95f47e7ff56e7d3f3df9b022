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
                "doctorWorkTimeStart" => $schedule->getWorkTimeStart() ? $schedule->getWorkTimeStart()->format('H:m:s'): null,
                "doctorWorkTimeEnd" => $schedule->getWorkTimeEnd() ? $schedule->getWorkTimeEnd()->format('H:m:s'): null,
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

    public function generateRecommendations($scheduleData): array
    {
        //TODO: На empty - проверки не хватает, если нет возможности закрыть все потребности.
        // Типа добавьте столько то врачей, чтобы закрыть потребность. Тут можно дернуть мой сервис,
        // как-то его под это подстроить, чтобы он прям вернул количество врачей, например из текущего списка,
        // только мы бы отдали, мол нужно ЕЩЕ столько то врачей с такими то режимами работы

        //TODO: Рекомендацию + в фитнес функцию сделай, проверку empty по модальностям. Мол если где-то намного больше empty,
        // чем на других модальностях, предложи сбалансировать + это мне тоже надо запилить
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
                    $recommendations['Учет перегрузки врачей'][] = [
                        "problem" => "Доктор $doctorId перегружен",
                        "solution" => "Рассмотрите возможность перераспределения задач",
                    ];
                }
            }

            // Проверка равномерности распределения задач
            $avgWorkload = array_sum($doctorWorkloads) / count($doctorWorkloads);
            foreach ($doctorWorkloads as $doctorId => $workload) {
                if (abs($workload - $avgWorkload) > 2) {
                    $recommendations['Проверка равномерности распределения задач'][] = [
                        "problem" => "Нагрузка на доктора $doctorId отклоняется от средней",
                        "solution" => "Попробуйте сбалансировать задачи",
                    ];
                }
            }

            // Минимизация перерывов
            foreach ($doctorDaysWorked as $doctorId => $daysWorked) {
                $days = array_keys($daysWorked);
                for ($i = 1; $i < count($days); $i++) {
                    if ($days[$i] - $days[$i - 1] > 1) {
                        $recommendations['Минимизация перерывов'][] = [
                            "problem" => "Доктор $doctorId имеет большие перерывы между рабочими днями",
                            "solution" => "Сократите перерывы",
                        ];
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

                //TODO: а если сутки через трое ?
                if ($maxConsecutiveDaysOff > 2) {
                    $recommendations['Минимизация выходных дней подряд'][] = [
                        "problem" => "Доктор $doctorId имеет более 2 выходных дней подряд",
                        "solution" => "Попробуйте их сократить",
                    ];
                }
            }

            // Учет времени работы и перерывов
            foreach ($doctorWorkTimes as $doctorId => $workTimes) {
                foreach ($workTimes as $workTime) {
                    list($start, $end) = $workTime;
                    if ($start !== null && $end !== null) {
                        $workDuration = (strtotime($end) - strtotime($start)) / 60; // Время работы в минутах
                        if ($workDuration > 480) { // если работа длится более 8 часов
                            $recommendations['Учет времени работы и перерывов'][] = [
                                "problem" => "Доктор $doctorId работает более 8 часов подряд",
                                "solution" => "Попробуйте сократить длительность смены",
                            ];
                        }
                    }
                }
            }

            // Учет минут перерыва
            foreach ($doctorOffMinutes as $doctorId => $offMinutes) {
                if ($offMinutes < 30) { // если перерывов менее 30 минут
                    $recommendations['Учет минут перерыва'][] = [
                        "problem" => "Доктор $doctorId имеет недостаточно времени на перерывы",
                        "solution" => "Увеличьте количество или длительность перерывов",
                    ];
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
                        $recommendations['Учет смен'][] = [
                            "problem" => "Доктор $doctorId имеет слишком много смен типа '$shiftType'",
                            "solution" => "Убедитесь в чередовании смен",
                        ];
                    }
                }
            }

            // Учет соответствия компетенций и модальностей исследований
            foreach ($doctorModalityAssignments as $doctorId => $modality) {
                if (!in_array($modality['studyModality'], $modality['doctorModality'])) {
                    $recommendations['Учет соответствия компетенций и модальностей исследований'][] = [
                        "problem" => "Доктор $doctorId назначен на исследование" . $modality['studyModality']. ", которое не соответствует его компетенциям.",
                        "solution" => "Назначте другого врача",
                    ];
                }
            }

            // Анализ нехватки или чрезмерного количества врачей в смене
            foreach ($shiftDoctors as $date => $shifts) {
                foreach ($shifts as $shiftType => $doctors) {
                    if (count($doctors) < 2) {
                        $recommendations['Анализ нехватки или чрезмерного количества врачей в смене'][] = [
                            "problem" => "В смене '$shiftType' на дату $date недостаточно врачей (меньше 2)",
                            "solution" => "Рассмотрите возможность добавления врачей",
                        ];
                    }
                    if (count($doctors) > 5) {
                        $recommendations['Анализ нехватки или чрезмерного количества врачей в смене'][] = [
                            "problem" => "В смене '$shiftType' на дату $date слишком много врачей (более 5)",
                            "solution" => "Рассмотрите возможность перераспределения",
                        ];
                    }
                }
            }

            // Учет недельных и месячных часов работы врача
            foreach ($doctorWeeklyHours as $doctorId => $weeklyHours) {
                foreach ($weeklyHours['hours'] as $weekNumber => $hours) {
                    $expectedWeeklyHours = 36 * $weeklyHours['rate'];
                    if ($hours > $expectedWeeklyHours) {
                        $recommendations['Учет недельных и месячных часов работы врача'][] = [
                            "problem" => "Доктор $doctorId работает более $expectedWeeklyHours часов в неделю",
                            "solution" => "Попробуйте сократить часы работы",
                        ];
                    }
                }
            }
            foreach ($doctorMonthlyHours as $doctorId => $monthlyHours) {
                foreach ($monthlyHours['hours'] as $month => $hours) {
                    if ($hours > 155 * $weeklyHours['rate']) {
                        $recommendations['Учет недельных и месячных часов работы врача'][] = [
                            "problem" => "Доктор $doctorId работает более " . (155 * $weeklyHours['rate']) . " часов в месяц ",
                            "solution" => "Попробуйте сократить часы работы",
                        ];
                    }
                }
            }

            //Запрет на дневную смену после ночной
            foreach ($doctorShifts as $doctorId => $shifts) {
                for ($i = 1; $i < count($shifts); $i++) {
                    if ($shifts[$i - 1] == 'Ночные смены' && strpos($shifts[$i], 'Дневные смены') !== false) {
                        $recommendations['Запрет на дневную смену после ночной'][] = [
                            "problem" => "Доктор $doctorId имеет дневную смену сразу после ночной",
                            "solution" => "Пересмотрите график",
                        ];
                    }
                }
            }

            //Запрет на три подряд смены по 11-12 часов
            foreach ($doctorWorkTimes as $doctorId => $workTimes) {
                $consecutiveLongShifts = 0;
                foreach ($workTimes as $workTime) {
                    list($start, $end) = $workTime;
                    $workDuration = (strtotime($end) - strtotime($start)) / 3600; // Время работы в часах
                    if ($workDuration >= 11) {
                        $consecutiveLongShifts++;
                        if ($consecutiveLongShifts >= 3) {
                            $recommendations['Запрет на три подряд смены по 11-12 часов'][] = [
                                "problem" => "Доктор $doctorId имеет три подряд смены по 11-12 часов",
                                "solution" => "Пересмотрите график",
                            ];
                            break;
                        }
                    } else {
                        $consecutiveLongShifts = 0;
                    }
                }
            }

            //Равномерное распределение часов в течение месяца
            foreach ($doctorMonthlyHours as $doctorId => $monthlyHours) {
                foreach ($monthlyHours as $month => $hours) {
                    $halfMonthHours = $hours / 2;
                    if (abs($halfMonthHours - ($hours / 2)) > 20) { // допустимое отклонение в часах
                        $recommendations['Равномерное распределение часов в течение месяца'][] = [
                            "problem" => "Доктор $doctorId имеет неравномерное распределение часов в месяце",
                            "solution" => "Попробуйте сбалансировать",
                        ];
                    }
                }
            }

            //Обязательный перерыв в 48 часов
            foreach ($doctorDaysWorked as $doctorId => $daysWorked) {
                $days = array_keys($daysWorked);
                for ($i = 1; $i < count($days); $i++) {
                    if ($days[$i] - $days[$i - 1] > 2) { // проверка на перерыв более 48 часов (2 дня)
                        $recommendations['Обязательный перерыв в 48 часов'][] = [
                            "problem" => "Доктор $doctorId не имеет обязательного перерыва в 48 часов между сменами",
                            "solution" => "Попробуйте сбалансировать",
                        ];
                        break;
                    }
                }
            }
        } catch (\Throwable) {

        }

        return $recommendations;
    }
}
