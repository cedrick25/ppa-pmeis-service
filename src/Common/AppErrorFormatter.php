<?php

namespace App\Common;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class AppErrorFormatter
{
    /**
     * @param ConstraintViolationListInterface $errors
     * @return array<string, string>
     */
    public function format(ConstraintViolationListInterface $errors): array
    {
        $collectedErrors = [];

        /** @var ConstraintViolationInterface $error */
        foreach ($errors as $error) {
            $collectedErrors[$error->getPropertyPath()] = $error->getMessage();
        }

        return $collectedErrors;
    }
}