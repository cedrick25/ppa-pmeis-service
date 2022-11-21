<?php

namespace App\Entity;

use App\Repository\SocialMarketingPersonInvolvedRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SocialMarketingPersonInvolvedRepository::class)
 */
class SocialMarketingPersonInvolved
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
    private $socialMarketingId;

    /**
     * @ORM\Column(type="integer")
     */
    private $personInvolvedId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $role;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $othersName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSocialMarketingId(): ?int
    {
        return $this->socialMarketingId;
    }

    public function setSocialMarketingId(int $socialMarketingId): self
    {
        $this->socialMarketingId = $socialMarketingId;

        return $this;
    }

    public function getPersonInvolvedId(): ?int
    {
        return $this->personInvolvedId;
    }

    public function setPersonInvolvedId(int $personInvolvedId): self
    {
        $this->personInvolvedId = $personInvolvedId;

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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

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
