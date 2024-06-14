<?php

declare(strict_types=1);

namespace App\Enum;

enum Holiday: string
{
    case NEW_YEAR = '01-01';
    case CHRISTMAS_DAY = '01-07';
    case DEFENDER_OF_THE_FATHERLAND_DAY = '02-23';
    case INTERNATIONAL_WOMENS_DAY = '03-08';
    case SPRING_AND_LABOR_FESTIVAL = '05-01';
    case VICTORY_DAY = '05-09';
    case RUSSIA_DAY = '06-12';
    case NATIONAL_UNITY_DAY = '11-04';

    private static function labels(): array
    {
        return [
            self::NEW_YEAR->value => 'Новый год',
            self::CHRISTMAS_DAY->value => 'Рождество Христово',
            self::DEFENDER_OF_THE_FATHERLAND_DAY->value => 'День защитника Отечества',
            self::INTERNATIONAL_WOMENS_DAY->value => 'Международный женский день',
            self::SPRING_AND_LABOR_FESTIVAL->value => 'Праздник весны и труда',
            self::VICTORY_DAY->value => 'День Победы',
            self::RUSSIA_DAY->value => 'День России',
            self::NATIONAL_UNITY_DAY->value => 'День народного единства',
        ];
    }

    private static function nextHoliday(): array
    {
        return [
            self::NEW_YEAR->value => self::CHRISTMAS_DAY,
            self::CHRISTMAS_DAY->value => self::DEFENDER_OF_THE_FATHERLAND_DAY,
            self::DEFENDER_OF_THE_FATHERLAND_DAY->value => self::INTERNATIONAL_WOMENS_DAY,
            self::INTERNATIONAL_WOMENS_DAY->value => self::SPRING_AND_LABOR_FESTIVAL,
            self::SPRING_AND_LABOR_FESTIVAL->value => self::VICTORY_DAY,
            self::VICTORY_DAY->value => self::RUSSIA_DAY,
            self::RUSSIA_DAY->value => self::NATIONAL_UNITY_DAY,
            self::NATIONAL_UNITY_DAY->value => self::NEW_YEAR,
        ];
    }

    public function label(): ?string
    {
        $labels = self::labels();

        return $labels[$this->value] ?? null;
    }

    public function getNextHoliday(): ?self
    {
        $nextHoliday = self::nextHoliday();

        return $nextHoliday[$this->value] ?? null;
    }

    public function convertDate(): string
    {
        list($month, $day) = explode('-', $this->value);

        return $day.' '.$this::findMonth($month);
    }

    public static function findMonth(string $month): string
    {
        return match ($month) {
            '01' => 'января',
            '02' => 'февраля',
            '03' => 'марта',
            '04' => 'апреля',
            '05' => 'мая',
            '06' => 'июня',
            '07' => 'июля',
            '08' => 'августа',
            '09' => 'сентября',
            '10' => 'октября',
            '11' => 'ноября',
            '12' => 'декабря',
            default => '',
        };
    }

    public static function findNextHolidays(): array
    {
        $currentDate = (new \DateTime())->modify('+3 hours');
        $currentYear = (int) $currentDate->format('Y');
        $holidays = self::cases();

        $nextHoliday = null;
        $nextHolidayDiff = PHP_INT_MAX;
        foreach ($holidays as $holiday) {
            $holidayDate = \DateTime::createFromFormat('m-d', $holiday->value);
            $holidayDate->setDate($currentYear, (int)substr($holiday->value, 0, 2), (int)substr($holiday->value, 3, 2));

            if ($holidayDate >= $currentDate) {
                $diff = $holidayDate->diff($currentDate)->days;
                if ($diff < $nextHolidayDiff) {
                    $nextHolidayDiff = $diff;
                    $nextHoliday = $holiday;
                }
            }
        }

        $holiday1 = $nextHoliday ?? self::NEW_YEAR;
        $holiday2 = $holiday1->getNextHoliday();
        $holiday3 = $holiday2->getNextHoliday();
        $holiday4 = $holiday3->getNextHoliday();

        return [
            'holiday1' => $holiday1->convertDate(),
            'holiday2'=> $holiday2->convertDate(),
            'holiday3'=> $holiday3->convertDate(),
            'holiday4' => $holiday4->convertDate(),
            'holidayName1' => $holiday1->label(),
            'holidayName2' => $holiday2->label(),
            'holidayName3' => $holiday3->label(),
            'holidayName4' => $holiday4->label(),
        ];
    }
}
