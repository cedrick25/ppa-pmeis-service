<?php

namespace App\Service\TherapeuticCommunity;

use App\Model\Sessions as SessionsModel;

interface SessionsInterface
{
    public function create(SessionsModel $sessionData): array;
}