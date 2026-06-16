<?php

namespace App\Entity;

use App\Repository\VpaAssociationActivityVolunteersServicesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VpaAssociationActivityVolunteersServicesRepository::class)
 */
class VpaAssociationActivityVolunteersServices
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
    private $vpaActivityVolunteerId;

    /**
     * @ORM\Column(type="integer")
     */
    private $serviceRenderedId;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVpaActivityVolunteerId(): ?int
    {
        return $this->vpaActivityVolunteerId;
    }

    public function setVpaActivityVolunteerId(int $vpaActivityVolunteerId): self
    {
        $this->vpaActivityVolunteerId = $vpaActivityVolunteerId;

        return $this;
    }

    public function getServiceRenderedId(): ?int
    {
        return $this->serviceRenderedId;
    }

    public function setServiceRenderedId(int $serviceRenderedId): self
    {
        $this->serviceRenderedId = $serviceRenderedId;

        return $this;
    }


}
