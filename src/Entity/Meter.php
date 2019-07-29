<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MeterRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Meter
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=36)
     *
     * @Assert\NotBlank()
     * @Assert\Uuid(versions={4})
     *
     * @var string
     */
    private $serial;

    /**
     * @ORM\Column(type="string", length=10)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min="3", max="10")
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    private $created;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    private $updated;

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = $this->updated = new \DateTime("now");
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated = new \DateTime("now");
    }

    /**
     * @return string
     */
    public function getSerial(): string
    {
        return $this->serial;
    }

    /**
     * @param string $serial
     *
     * @return Meter
     */
    public function setSerial(string $serial): Meter
    {
        $this->serial = $serial;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Meter
     */
    public function setName(string $name): Meter
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     *
     * @return Meter
     */
    public function setCreated(\DateTime $created): Meter
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    /**
     * @param \DateTime $updated
     *
     * @return Meter
     */
    public function setUpdated(\DateTime $updated): Meter
    {
        $this->updated = $updated;

        return $this;
    }
}
