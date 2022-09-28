<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\QuartersRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\Quarters as QuartersEnum;

/**
 * @ORM\Entity(repositoryClass=QuartersRepository::class)
 */
class Quarters
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $quarterId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $year;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;

    public function getQuarterId(): ?int
    {
        return $this->quarterId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setName(string $name): self
    {
        if (! QuartersEnum::isValid($name)) {
            throw new InvalidArgumentException("Invalid Quarter");
        }
        $this->name = $name;

        return $this;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(string $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
