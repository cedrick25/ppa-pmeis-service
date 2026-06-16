<?php

namespace App\Entity;

use App\Repository\PmdPersonResponsibleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PmdPersonResponsibleRepository::class)
 */
class PmdPersonResponsible
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer")
     */
    private int $pmdId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $personResponsibleId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private string $othersName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPmdId(): ?int
    {
        return $this->pmdId;
    }

    public function setPmdId(int $pmdId): self
    {
        $this->pmdId = $pmdId;

        return $this;
    }

    public function getPersonResponsibleId(): ?int
    {
        return $this->personResponsibleId;
    }

    public function setPersonResponsibleId(int $personResponsibleId): self
    {
        $this->personResponsibleId = $personResponsibleId;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOthersName(): ?string
    {
        return $this->othersName;
    }

    public function setOthersName(?string $othersName): self
    {
        $this->othersName = $othersName;

        return $this;
    }
}
