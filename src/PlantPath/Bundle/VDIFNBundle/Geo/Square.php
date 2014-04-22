<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo;

class Square
{
    /**
     * @var PlantPath\Bundle\VDIFNBundle\Geo\Point
     */
    protected $center;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var float
     */
    protected $latitudinalHeight;

    /**
     * @var float
     */
    protected $longitudinalWidth;

    /**
     * Constructor.
     *
     * @param Point  $center
     * @param int    $size   The size (width/height) of the square in KM.
     */
    public function __construct(Point $center, $size)
    {
        $this
            ->setCenter($center)
            ->setSize($size);
    }

    /**
     * Sets the value of center.
     *
     * @param Point $center
     *
     * @return self
     */
    public function setCenter(Point $center)
    {
        $this->center = $center;

        return $this;
    }

    /**
     * Gets the value of center.
     *
     * @return Point
     */
    public function getCenter()
    {
        return $this->center;
    }

    /**
     * Computes the latitudinal height of this square.
     *
     * @return float
     */
    public function getLatitudinalHeight()
    {
        if (null === $this->latitudinalHeight) {
            $this->latitudinalHeight = GeoUtils::kmToLatitude($this->size);
        }

        return $this->latitudinalHeight;
    }

    /**
     * Computes the longitudinal width of this square.
     *
     * @return float
     */
    public function getLongitudinalWidth()
    {
        if (null === $this->longitudinalWidth) {
            $this->longitudinalWidth = GeoUtils::kmToLongitude($this->size, $this->center->getLatitude());
        }

        return $this->longitudinalWidth;
    }

    /**
     * Gets the southwest point.
     *
     * @return Point
     */
    public function getSouthWestCorner()
    {
        return new Point(
            $this->center->getLatitude() - $this->getLatitudinalHeight() / 2,
            $this->center->getLongitude() - $this->getLongitudinalWidth() / 2
        );
    }

    /**
     * Gets the northwest point.
     *
     * @return Point
     */
    public function getNorthWestCorner()
    {
        return new Point(
            $this->center->getLatitude() + $this->getLatitudinalHeight() / 2,
            $this->center->getLongitude() - $this->getLongitudinalWidth() / 2
        );
    }

    /**
     * Gets the northwest point.
     *
     * @return Point
     */
    public function getNorthEastCorner()
    {
        return new Point(
            $this->center->getLatitude() + $this->getLatitudinalHeight() / 2,
            $this->center->getLongitude() + $this->getLongitudinalWidth() / 2
        );
    }

    /**
     * Gets the southeast point.
     *
     * @return Point
     */
    public function getSouthEastCorner()
    {
        return new Point(
            $this->center->getLatitude() - $this->getLatitudinalHeight() / 2,
            $this->center->getLongitude() + $this->getLongitudinalWidth() / 2
        );
    }

    /**
     * Sets the value of size.
     *
     * @param int $size the size
     *
     * @return self
     */
    public function setSize($size)
    {
        if (false === $size = filter_var($size, FILTER_VALIDATE_INT)) {
            throw new \UnexpectedValueException('Could not validate size as an integer: ' . $size);
        }

        $this->size = $size;

        return $this;
    }

    /**
     * Gets the value of size.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }
}
