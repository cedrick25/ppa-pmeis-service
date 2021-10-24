<?php

namespace App\Common;

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
}