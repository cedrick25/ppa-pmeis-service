<?php

declare(strict_types=1);

namespace App\Service\Cmis;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Model\Clients as ClientModel;
use App\Repository\ClientsRepository;
use App\Repository\CmisClientRepositoryRegistry;
use Psr\Log\LoggerInterface;

class CmisClientSyncService
{
    public function __construct(
        private AppFormatter $appFormatter,
        private CmisClientRepositoryRegistry $repositoryRegistry,
        private CmisClientMapper $mapper,
        private ClientsRepository $clientsRepository,
        private LoggerInterface $logger,
        private string $appEnvironment,
    ){}

    public function preview(
        string $cmisSource,
        int $pageSize,
        ?int $fieldOfficeId,
        ?string $yearMonth,
        ?string $search,
        ?int $cursor = null
    ): array {
        if (!CmisSource::isValid($cmisSource)) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::VALIDATING_FAILED,
                null,
                ['cmis' => 'Unsupported CMIS source.']
            );
        }

        $yearMonth = $yearMonth !== null ? trim($yearMonth) : '';
        $search = $search !== null ? trim($search) : '';

        if ($yearMonth === '' && $search === '') {
            return $this->appFormatter->formatResponse(
                ResponseEnum::VALIDATING_FAILED,
                null,
                ['cmis' => 'A year_month or search filter is required.']
            );
        }

        try {
            $repository = $this->repositoryRegistry->get($cmisSource);
            $data = $repository->paginated($pageSize, $fieldOfficeId, $yearMonth ?: null, $search ?: null, $cursor);
            $data['items'] = array_map(
                fn (array $row) => $this->previewRow($row, $cmisSource),
                $data['items']
            );
            $data['cmisSource'] = $cmisSource;

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $data);
        } catch (\Throwable $exception) {
            $this->logger->error('CMIS preview failed.', [
                'source' => $cmisSource,
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            $errors = ['cmis' => sprintf('Unable to fetch CMIS %s clients.', CmisSource::label($cmisSource))];
            if ($this->appEnvironment === 'dev') {
                $errors['detail'] = $exception->getMessage();
            }

            return $this->appFormatter->formatResponse(
                ResponseEnum::FETCHING_FAILED,
                null,
                $errors
            );
        }
    }

    public function syncOne(string $cmisSource, int $cmisId): array
    {
        if (!CmisSource::isValid($cmisSource)) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::VALIDATING_FAILED,
                null,
                ['cmis' => 'Unsupported CMIS source.']
            );
        }

        try {
            $row = $this->repositoryRegistry->get($cmisSource)->findActiveById($cmisId);

            if (!$row) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_SUCCESS,
                $this->syncRow($row, $cmisSource)
            );
        } catch (\Throwable $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['cmis' => 'Unable to sync CMIS client.']
            );
        }
    }

    /**
     * @param int[]|null $cmisIds
     */
    public function sync(
        string $cmisSource,
        ?int $fieldOfficeId,
        ?string $yearMonth,
        ?array $cmisIds,
        int $limit
    ): array {
        if (!CmisSource::isValid($cmisSource)) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::VALIDATING_FAILED,
                null,
                ['cmis' => 'Unsupported CMIS source.']
            );
        }

        try {
            $rows = $this->repositoryRegistry->get($cmisSource)->findActiveForSync(
                $fieldOfficeId,
                $yearMonth,
                $cmisIds,
                $limit
            );
            $summary = [
                'created' => 0,
                'updated' => 0,
                'linked' => 0,
                'skipped' => 0,
                'failed' => 0,
                'items' => [],
            ];

            foreach ($rows as $row) {
                $result = $this->syncRow($row, $cmisSource);
                $action = $result['action'];

                if (isset($summary[$action])) {
                    $summary[$action]++;
                }

                $summary['items'][] = $result;
            }

            return $this->appFormatter->formatResponse(ResponseEnum::UPDATING_SUCCESS, $summary);
        } catch (\Throwable $exception) {
            return $this->appFormatter->formatResponse(
                ResponseEnum::UPDATING_FAILED,
                null,
                ['cmis' => sprintf('Unable to sync CMIS %s clients.', CmisSource::label($cmisSource))]
            );
        }
    }

    public function getByCmisId(string $cmisSource, int $cmisId): array
    {
        $client = $this->clientsRepository->findOneActiveByCmisSourceAndId($cmisSource, $cmisId);

        if ($client === null) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, [
            'clientId' => $client->getClientId(),
            'cmisId' => $client->getCmisId(),
            'cmisSource' => $client->getCmisSource(),
            'firstName' => $client->getFirstName(),
            'lastName' => $client->getLastName(),
            'fullName' => $client->getFullName(),
            'fieldOfficeId' => $client->getFieldOfficeId(),
        ]);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function previewRow(array $row, string $cmisSource): array
    {
        $mapped = $this->mapper->map($row, $cmisSource);
        $cmisId = (int) ($row['id'] ?? 0);
        $status = 'ready';
        $clientId = null;

        if ($mapped['errors'] !== []) {
            $status = 'invalid';
        } elseif ($cmisId > 0 && ($client = $this->clientsRepository->findOneActiveByCmisSourceAndId($cmisSource, $cmisId)) !== null) {
            $status = 'linked';
            $clientId = $client->getClientId();
        } elseif ($mapped['client'] instanceof ClientModel && $this->hasDuplicate($mapped['client'])) {
            $status = 'duplicate';
        }

        return array_merge($mapped['source'], [
            'status' => $status,
            'clientId' => $clientId,
            'errors' => $mapped['errors'],
        ]);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function syncRow(array $row, string $cmisSource): array
    {
        $mapped = $this->mapper->map($row, $cmisSource);
        $source = $mapped['source'];

        if (!$mapped['client'] instanceof ClientModel) {
            return array_merge($source, [
                'action' => 'failed',
                'errors' => $mapped['errors'],
            ]);
        }

        $clientData = $mapped['client'];
        $existing = $this->clientsRepository->findOneActiveByCmisSourceAndId(
            $cmisSource,
            (int) $clientData->getCmisId()
        );

        try {
            if ($existing !== null) {
                $this->clientsRepository->updateFromCmis($existing, $clientData);

                return array_merge($source, [
                    'action' => 'updated',
                    'clientId' => $existing->getClientId(),
                ]);
            }

            $linked = $this->findLinkableDuplicate($clientData);
            if ($linked['status'] === 'ambiguous') {
                return array_merge($source, [
                    'action' => 'skipped',
                    'errors' => ['duplicate' => 'Multiple possible PMEIS client matches found.'],
                ]);
            }

            if ($linked['client'] !== null) {
                $this->clientsRepository->updateFromCmis($linked['client'], $clientData);

                return array_merge($source, [
                    'action' => 'linked',
                    'clientId' => $linked['client']->getClientId(),
                ]);
            }

            $clientId = $this->clientsRepository->createFromCmis($clientData);

            return array_merge($source, [
                'action' => 'created',
                'clientId' => $clientId,
            ]);
        } catch (\Throwable $exception) {
            return array_merge($source, [
                'action' => 'failed',
                'errors' => ['app' => 'Unable to save PMEIS client from CMIS row.'],
            ]);
        }
    }

    private function hasDuplicate(ClientModel $clientData): bool
    {
        $linked = $this->findLinkableDuplicate($clientData);

        return $linked['client'] !== null || $linked['status'] === 'ambiguous';
    }

    /**
     * @return array{status: string, client: \App\Entity\Clients|null}
     */
    private function findLinkableDuplicate(ClientModel $clientData): array
    {
        $docketNo = $clientData->getCmisDocketNo();
        if ($docketNo !== null && $docketNo !== '') {
            $client = $this->clientsRepository->findOneActiveByCmisDocketNo($docketNo);
            if ($client !== null) {
                return ['status' => 'matched', 'client' => $client];
            }
        }

        $candidates = $this->clientsRepository->findActiveDuplicateCandidates($clientData);
        if (count($candidates) > 1) {
            return ['status' => 'ambiguous', 'client' => null];
        }

        if (count($candidates) === 1) {
            $client = $this->clientsRepository->isExistingById((int) $candidates[0]['client_id']);

            return [
                'status' => $client === false ? 'none' : 'matched',
                'client' => $client === false ? null : $client,
            ];
        }

        return ['status' => 'none', 'client' => null];
    }
}
