<?php

namespace App\Service\TherapeuticCommunity;

use App\Model\Sessions as SessionsModel;

interface SessionsInterface
{
    public function create(SessionsModel $sessionData): array;

    public function getAll(): array;

    public function deleteById(int $id): array;

    public function updateById(int $id, SessionsModel $sessionData):array;

    public function updateByIdWithClientAndFacilitators(int $id, SessionsModel $sessionData):array;

    public function getById(int $id): array;

    public function getPaginated(int $page, int $pageSize): array;

    public function createWithClientsAndFacilitators(SessionsModel $sessionData): array;

    public function getAllWithClientsAndFacilitators(): array;
}