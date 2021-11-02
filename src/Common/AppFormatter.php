<?php

namespace App\Common;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class AppFormatter
{
    /**
     * @param ConstraintViolationListInterface $errors
     * @return array<string, string>
     */
    public function formatErrors(ConstraintViolationListInterface $errors): array
    {
        $collectedErrors = [];

        /** @var ConstraintViolationInterface $error */
        foreach ($errors as $error) {
            $collectedErrors[$error->getPropertyPath()] = $error->getMessage();
        }

        return $collectedErrors;
    }


    /**
     * @param string $message
     * @param array<string, mixed>|null $data
     * @param array<string, string>|null $errors
     * @return array<string, mixed>
     */
    public function formatResponse(string $message, ?array $data, ?array $errors = null): array
    {
        $response = ['message' => $message];
        if ($data != null) {
            $response['data'] = $data;
        }
        if ($errors != null) {
            $response['errors'] = $errors;
        }

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    public function formatPagination($totalItems, $pageCount, $pageItems): array
    {
        return [
            "itemCount" => $totalItems,
            "pageCount" => $pageCount,
            "items" => $pageItems
        ];
    }
}