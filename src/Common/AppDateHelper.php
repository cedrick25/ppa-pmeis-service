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
    public function convertStringToImmutableDate(string $date): DateTimeImmutable
    {
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
}