<?php

namespace App\Entity;

use App\Repository\RJVolunteersRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RJVolunteersRepository::class)
 */
class RJVolunteers
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $rjVolunteersId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $volunteerId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $relatedActivityId;

    public function getRJVolunteersId(): ?int
    {
        return $this->rjVolunteersId;
    }

    public function getVolunteerId(): ?int
    {
        return $this->volunteerId;
    }

    public function setVolunteerId(int $volunteerId): self
    {
        $this->volunteerId = $volunteerId;

        return $this;
    }

    public function getRelatedActivityId(): ?int
    {
        return $this->relatedActivityId;
    }

    public function setRelatedActivityId(int $relatedActivityId): self
    {
        $this->relatedActivityId = $relatedActivityId;

        return $this;
    }
}
