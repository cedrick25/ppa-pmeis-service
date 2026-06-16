<?php

namespace App\Entity;

use App\Repository\RjConductedProcessPersonsInvolvedRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RjConductedProcessPersonsInvolvedRepository::class)
 */
class RjConductedProcessPersonsInvolved
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
    private $rjConductedProcessId;

    /**
     * @ORM\Column(type="integer")
     */
    private $personsInvolvedId;

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

    public function getRjConductedProcessId(): ?int
    {
        return $this->rjConductedProcessId;
    }

    public function setRjConductedProcessId(int $rjConductedProcessId): self
    {
        $this->rjConductedProcessId = $rjConductedProcessId;

        return $this;
    }

    public function getPersonsInvolvedId(): ?int
    {
        return $this->personsInvolvedId;
    }

    public function setPersonsInvolvedId(int $personsInvolvedId): self
    {
        $this->personsInvolvedId = $personsInvolvedId;

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
