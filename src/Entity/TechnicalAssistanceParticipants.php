<?php

namespace App\Entity;

use App\Repository\TechnicalAssistanceParticipantsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TechnicalAssistanceParticipantsRepository::class)
 */
class TechnicalAssistanceParticipants
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
    private int $technicalAssistanceId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $no;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTechnicalAssistanceId(): ?int
    {
        return $this->technicalAssistanceId;
    }

    public function setTechnicalAssistanceId(int $technicalAssistanceId): self
    {
        $this->technicalAssistanceId = $technicalAssistanceId;

        return $this;
    }

    public function getNo(): ?string
    {
        return $this->no;
    }

    public function setNo(string $no): self
    {
        $this->no = $no;

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
}
