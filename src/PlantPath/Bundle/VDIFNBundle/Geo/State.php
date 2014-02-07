<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo;

use FM\Geo\PointUtils;

class State
{
    /**
     * The name of the state.
     *
     * @var string
     */
    protected $name;

    /**
     * The latitudinal/longitudinal boundaries of the state.
     *
     * @var array
     */
    protected $boundaries;

    /**
     * Constructor.
     *
     * @param string $name The name of the state.
     */
    public function __construct($name, $boundaries = [])
    {
        $this
            ->setName($name)
            ->setBoundaries($boundaries);
    }

    /**
     * Gets the name of the state.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name of the state.
     *
     * @param string $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Determines whether the lat/long point is within this state.
     *
     * @param  PlantPath\Bundle\VDIFNBundle\Geo\Point $point
     *
     * @return boolean
     */
    public function containsPoint(Point $point)
    {
        return PointUtils::pointInPolygon($point->toArray(), $this->boundaries);
    }

    /**
     * Gets the latitudinal/longitudinal boundaries of the state.
     *
     * @return array
     */
    public function getBoundaries()
    {
        return $this->boundaries;
    }

    /**
     * Sets the latitudinal/longitudinal boundaries of the state.
     *
     * @param array $boundaries the boundaries
     *
     * @return self
     */
    public function setBoundaries(array $boundaries)
    {
        if (!empty($boundaries)) {
            foreach ($boundaries as $point) {
                if (!is_array($point) || 2 !== count($point)) {
                    throw new \InvalidArgumentException('Boundaries must be an array of 2-tuple arrays');
                }
            }
        }

        $this->boundaries = $boundaries;

        return $this;
    }
}
