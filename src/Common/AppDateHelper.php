<?php

namespace App\Common;

use DateTime;
use DateTimeImmutable;
use Exception;

class AppDateHelper
{
    public function getCurrentImmutableDate(): DateTimeImmutable
    {
        $currentDateTime = new DateTimeImmutable();
        $currentDateTime->format("Y-m-d H:m:s");

        return $currentDateTime;
    }

    /**
     * @throws Exception
     */
    public function convertStringToImmutableDate(?string $date): ?DateTimeImmutable
    {
        if ($date === null) {
            return null;
        }

        $date = new DateTimeImmutable($date);
        $date->format("Y-m-d");

        return $date;
    }

    public function getFirstLetterOfMonthFromDateString(string $date): string
    {
        $time = strtotime($date);
        $month = date("m",$time);
        $dt = DateTime::createFromFormat('!m', $month);

        return substr($dt->format('F'), 0, 1);
    }

    /**
     * @param string $quarter
     * @return int[]
     */
    public function getMonthsByQuarterString(string $quarter): array
    {
        $data = [
            'FIRST' => [1,2,3],
            'SECOND' => [4,5,6],
            'THIRD' => [7,8,9],
            'FOURTH' => [10,11,12]
        ];

        return $data[$quarter];
    }

    public function getQuarterByMonth(int $month): string
    {
        $data = [
            1 => 'FIRST', 2 => 'FIRST' , 3 => 'FIRST',
            4 => 'SECOND', 5 => 'SECOND' , 6 => 'SECOND',
            7 => 'THIRD', 8 => 'THIRD' , 9 => 'THIRD',
            10 => 'FOURTH', 11 => 'FOURTH' , 12 => 'FOURTH'
        ];

        return $data[$month];
    }

    public function getMinMaxDateByYearsAndMonths(array $years, $months): array
    {
        $years = array_unique($years);
        $minYear = min($years);
        $maxYear = max($years);

        $months = array_unique($months);
        $minMonth = min($months);
        $minMonth = intval($minMonth) <= 9 ? '0' . $minMonth : $minMonth;
        $maxMonth = max($months);

        $minDate = $minYear . '-' . $minMonth . '-1';
        $maxDate = new \DateTime($maxYear . '-' . $maxMonth . '-15');
        $maxDate = $maxDate->format('Y-m-t');

        return [
            'min' => $minDate,
            'max' => $maxDate
        ];
    }

    private function getMonthNameByMonthNumber(int $monthNumber): string
    {
        $dateObj   = DateTime::createFromFormat('!m', $monthNumber);

        return $dateObj->format('F');
    }
}