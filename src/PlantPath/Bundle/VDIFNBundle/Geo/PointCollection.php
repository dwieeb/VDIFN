<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo;

class PointCollection
{
    /**
     * @var array
     */
    protected $points = [];

    /**
     * @var array
     */
    protected $arrayCache = [];

    /**
     * @var boolean
     */
    protected $arrayCacheOutdated = true;

    /**
     * Constructor.
     *
     * @param array $points
     */
    public function __construct(array $points = [])
    {
        $this->addPoints($points);
    }

    /**
     * Gets the value of points.
     *
     * @return mixed
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Sets the value of points.
     *
     * @param array $points the points
     *
     * @return self
     */
    public function setPoints(array $points)
    {
        $this->points = [];
        $this->addPoints($points);

        return $this;
    }

    /**
     * Adds an array of points to this PointCollection.
     *
     * @param array $points
     *
     * @return self
     */
    public function addPoints(array $points)
    {
        foreach ($points as $point) {
            $this->addPoint($point);
        }

        return $this;
    }

    /**
     * Adds a Point to this PointCollection.
     *
     * @param  PlantPath\Bundle\VDIFNBundle\Geo\Point $point
     *
     * @return self
     */
    public function addPoint(Point $point)
    {
        $this->points[] = $point;
        $this->arrayCacheOutdated = true;

        return $this;
    }

    /**
     * Transform this PointCollection into an array of 2-tuples representing
     * the points in this collection.
     *
     * @return array
     */
    public function toArray()
    {
        if ($this->arrayCacheOutdated) {
            $points = [];

            foreach ($this->getPoints() as $point) {
                $points[] = $point->toArray();
            }

            $this->arrayCache = $points;
            $this->arrayCacheOutdated = false;
        }

        return $this->arrayCache;
    }
}
