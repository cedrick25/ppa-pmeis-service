<?php

namespace App\Model;
use App\Common\AppHydrator;
use Symfony\Component\Validator\Constraints as Assert;

class ClientTypes implements \JsonSerializable
{
    public function __construct(
        private string $code,
        private string $description,
    ){}

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @Assert\NotBlank
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    public function jsonSerialize(): array
    {
        $hydrate = new AppHydrator();

        return $hydrate->convertObjectToArray($this);
    }
}