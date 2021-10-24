<?php

namespace App\Common;

use DateTimeImmutable;

class AppDateHelper
{
    public function getCurrentImmutableDate(): DateTimeImmutable
    {
        $currentDateTime = new DateTimeImmutable();
        $currentDateTime->format("Y-m-d H:m:s");

        return $currentDateTime;
    }
}