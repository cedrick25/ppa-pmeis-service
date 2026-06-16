<?php

namespace App\Entity;

use App\Repository\JailDecongestionPersonResponsibleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=JailDecongestionPersonResponsibleRepository::class)
 */
class JailDecongestionPersonResponsible
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $jailDecongestionId;

    /**
     * @ORM\Column(type="integer")
     */
    private $personResponsibleId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $othersName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJailDecongestionId(): ?int
    {
        return $this->jailDecongestionId;
    }

    public function setJailDecongestionId(int $jailDecongestionId): self
    {
        $this->jailDecongestionId = $jailDecongestionId;

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
