<?php

namespace App\Service\RestorativeJustice;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\PaymentModesRepository;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

class PaymentModes implements PaymentModesInterface
{
    public function __construct(
        private AppFormatter $appFormatter,
        private PaymentModesRepository $repository,
    ){}

    public function getAll(): array
    {
        try {
            $paymentModes = $this->repository->list();

            if ($paymentModes == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $paymentModes);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $paymentMode = $this->repository->isExistingById($id);

        if (!$paymentMode) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $paymentMode);
    }

}