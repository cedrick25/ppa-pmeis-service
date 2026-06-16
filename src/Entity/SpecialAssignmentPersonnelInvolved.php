<?php

namespace App\Entity;

use App\Repository\SpecialAssignmentPersonnelInvolvedRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SpecialAssignmentPersonnelInvolvedRepository::class)
 */
class SpecialAssignmentPersonnelInvolved
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
    private $specialAssignmentId;

    /**
     * @ORM\Column(type="integer")
     */
    private $personnelInvolvedId;

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

    public function getSpecialAssignmentId(): ?int
    {
        return $this->specialAssignmentId;
    }

    public function setSpecialAssignmentId(int $specialAssignmentId): self
    {
        $this->specialAssignmentId = $specialAssignmentId;

        return $this;
    }

    public function getPersonnelInvolvedId(): ?int
    {
        return $this->personnelInvolvedId;
    }

    public function setPersonnelInvolvedId(int $personnelInvolvedId): self
    {
        $this->personnelInvolvedId = $personnelInvolvedId;

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
