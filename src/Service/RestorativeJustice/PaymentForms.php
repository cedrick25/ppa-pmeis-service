<?php

namespace App\Service\RestorativeJustice;

use App\Common\AppFormatter;
use App\Enum\Response as ResponseEnum;
use App\Repository\PaymentFormsRepository;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;

class PaymentForms implements PaymentFormsInterface
{
    public function __construct(
        private AppFormatter $appFormatter,
        private PaymentFormsRepository $repository,
    ){}

    public function getAll(): array
    {
        try {
            $paymentForms = $this->repository->list();

            if ($paymentForms == null) {
                return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
            }

            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $paymentForms);
        } catch (CacheException|InvalidArgumentException $exception) {
            return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_FAILED, null, ['cache' => $exception->getMessage()]);
        }
    }

    public function getById(int $id): array
    {
        $paymentForm = $this->repository->isExistingById($id);

        if (!$paymentForm) {
            return $this->appFormatter->formatResponse(ResponseEnum::NO_DATA, null);
        }

        return $this->appFormatter->formatResponse(ResponseEnum::FETCHING_SUCCESS, $paymentForm);
    }
}