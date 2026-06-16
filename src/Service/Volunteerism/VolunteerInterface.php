<?php

namespace App\Service\Volunteerism;

use App\Model\Volunteer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface VolunteerInterface
{
    public function create(Volunteer $volunteerData): array;

    public function getAll(): array;

    public function getByFieldOffice(int $fieldOfficeId): array;

    public function deleteById(int $id): array;

    public function updateById(int $id, Volunteer $volunteerData):array;

    public function getById(int $id): array;

    public function getPaginated(string $status, int $page, int $pageSize, int $filedOfficeId): array;

    public function getByFieldOfficeAndMonthRange(int $fieldOfficeId, int $quarterId): array;

    public function getApplicants(): array;

    public function updateVolunteerStatus(array $data): array;

    public function getConsolidatedSocioDemographic(int $regionId): array;

    public function getVPADatabase(int $regionId, int $fieldOfficeId = null): array;

    public function getVpaMonitoring(int $quarterId, int $fieldOfficeId): array;

    public function getCertificate(array $data): string;

    public function getId(array $data): string;

    public function getIdData(array $data): array;

    public function updateDateAppointedById(int $id): array;
}
