<?php

namespace PlantPath\Bundle\VDIFNBundle\Entity;

use PlantPath\Bundle\VDIFNBundle\Geo\Point;
use Doctrine\ORM\Mapping as ORM;

/**
 * Subscriptions.
 *
 * @ORM\Entity
 * @ORM\Table(
 *     name="subscriptions",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="user_id_latitude_longitude_idx",
 *             columns={"user_id", "latitude", "longitude"}
 *         )
 *     }
 * )
 */
class Subscription
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \PlantPath\Bundle\VDIFNBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="PlantPath\Bundle\VDIFNBundle\Entity\User")
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(name="threshold", type="text", nullable=false)
     */
    protected $threshold;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="float", nullable=false)
     */
    protected $latitude;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="float", nullable=false)
     */
    protected $longitude;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    protected $active = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="active_at", type="utcdatetime", nullable=false)
     */
    protected $activeAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="utcdatetime", nullable=false)
     */
    protected $createdAt;

    /**
     * Factory.
     *
     * @return \PlantPath\Bundle\VDIFNBundle\Entity\Subscription
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        $now = new \DateTime();
        $this->setActiveAt($now);
        $this->setCreatedAt($now);
    }

    /**
     * Get the pretty version of this subscription's threshold.
     *
     * @return string
     */
    public function getPrettyThreshold()
    {
        switch ($this->getThreshold()) {
        case 'very_high':
            return 'Very High';
        case 'high':
            return 'High';
        case 'medium':
            return 'Medium';
        case 'low':
            return 'Low';
        case 'very_low':
            return 'Very Low';
        }
    }

    /**
     * Get id.
     *
     * @return id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get user.
     *
     * @return user.
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param user the value to set.
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get threshold.
     *
     * @return threshold.
     */
    public function getThreshold()
    {
        return $this->threshold;
    }

    /**
     * Set threshold.
     *
     * @param threshold the value to set.
     */
    public function setThreshold($threshold)
    {
        $this->threshold = $threshold;

        return $this;
    }

    /**
     * Set the value of latitude and longitude.
     *
     * @param  PlantPath\Bundle\VDIFNBundle\Geo\Point $point
     */
    public function setPoint(Point $point)
    {
        $this
            ->setLatitude($point->getLatitude())
            ->setLongitude($point->getLongitude());

        return $this;
    }

    /**
     * Get latitude.
     *
     * @return latitude.
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set latitude.
     *
     * @param latitude the value to set.
     */
    public function setLatitude($latitude)
    {
        if ($latitude !== null && false === $latitude = filter_var($latitude, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate latitude as a float: ' . $latitude);
        }

        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get longitude.
     *
     * @return longitude.
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set longitude.
     *
     * @param longitude the value to set.
     */
    public function setLongitude($longitude)
    {
        if ($longitude !== null && false === $longitude = filter_var($longitude, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate longitude as a float: ' . $longitude);
        }

        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get active.
     *
     * @return active.
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set active.
     *
     * @param active the value to set.
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get activeAt.
     *
     * @return activeAt.
     */
    public function getActiveAt()
    {
        return $this->activeAt;
    }

    /**
     * Set activeAt.
     *
     * @param activeAt the value to set.
     */
    public function setActiveAt(\DateTime $activeAt)
    {
        $this->activeAt = $activeAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return createdAt.
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt.
     *
     * @param createdAt the value to set.
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
