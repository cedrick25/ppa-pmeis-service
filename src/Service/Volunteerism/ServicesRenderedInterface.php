<?php

namespace App\Service\Volunteerism;

interface ServicesRenderedInterface
{
    public function getAll(): array;

    public function getById(int $id): array;
}