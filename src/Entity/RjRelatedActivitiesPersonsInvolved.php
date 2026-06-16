<?php

namespace App\Entity;

use App\Repository\RjRelatedActivitiesPersonsInvolvedRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RjRelatedActivitiesPersonsInvolvedRepository::class)
 */
class RjRelatedActivitiesPersonsInvolved
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
    private int $relatedActivityId;

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

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getRelatedActivityId(): int
    {
        return $this->relatedActivityId;
    }

    /**
     * @param int $relatedActivityId
     */
    public function setRelatedActivityId(int $relatedActivityId): void
    {
        $this->relatedActivityId = $relatedActivityId;
    }

    /**
     * @return mixed
     */
    public function getPersonsInvolvedId()
    {
        return $this->personsInvolvedId;
    }

    /**
     * @param mixed $personsInvolvedId
     */
    public function setPersonsInvolvedId($personsInvolvedId): void
    {
        $this->personsInvolvedId = $personsInvolvedId;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getOthersName()
    {
        return $this->othersName;
    }

    /**
     * @param mixed $othersName
     */
    public function setOthersName($othersName): void
    {
        $this->othersName = $othersName;
    }
}
