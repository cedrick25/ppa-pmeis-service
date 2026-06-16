<?php

namespace App\Entity;

use App\Repository\SocialMarketingParticipantRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SocialMarketingParticipantRepository::class)
 */
class SocialMarketingParticipant
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
     * @ORM\Column(type="string", length=255)
     */
    private $no;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

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
