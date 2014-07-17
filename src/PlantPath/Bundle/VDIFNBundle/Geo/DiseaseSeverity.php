<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo;

class DiseaseSeverity
{
    /**
     * @var array
     */
    protected static $dsvMatrix;

    /**
     * @var float
     */
    protected $meanTemperature;

    /**
     * @var int
     */
    protected $leafWettingTime;

    /**
     * Factory.
     *
     * @param  float $meanTemperature
     * @param  int $leafWettingTime
     *
     * @return self
     */
    public static function create($meanTemperature, $leafWettingTime)
    {
        return new self($meanTemperature, $leafWettingTime);
    }

    /**
     * Constructor.
     *
     * @param float $meanTemperature
     * @param int $leafWettingTime
     */
    public function __construct($meanTemperature, $leafWettingTime)
    {
        $this
            ->setMeanTemperature($meanTemperature)
            ->setLeafWettingTime($leafWettingTime);
    }

    /**
     * Given an array of objects that allow calculation of DSVs, calculate the
     * mean temperature and leaf-wetting time of that group of hourlies.
     *
     * @param  array  $hourlies
     * @param  int    $threshold
     *
     * @return array
     */
    public static function calculateTemperatureAndLeafWettingTime(array $hourlies, $threshold)
    {
        $leafWettingTime = 0;
        $sumTemperature = 0;

        foreach ($hourlies as $hourly) {
            if (!($hourly instanceof DsvCalculableInterface)) {
                throw new \RuntimeException('Hourly is missing implementation detail.');
            }

            if ($hourly->getRelativeHumidity() > $threshold) {
                $leafWettingTime += 1;
            }

            $sumTemperature += $hourly->getTemperature();
        }

        $meanTemperature = $sumTemperature / count($hourlies);

        return [$meanTemperature, $leafWettingTime];
    }

    /**
     * Builds the DSV Matrix if not already built and returns it.
     *
     * @return array
     */
    protected static function getDsvMatrix()
    {
        if (null === self::$dsvMatrix) {
            self::$dsvMatrix = [];

            $a = array_merge(
                array_fill_keys(range(0, 6), 0),
                array_fill_keys(range(7, 15), 1),
                array_fill_keys(range(16, 20), 2),
                array_fill_keys(range(21, 24), 3)
            );

            foreach (range(13, 17) as $i) {
                self::$dsvMatrix[$i] =& $a;
            }

            $b = array_merge(
                array_fill_keys(range(0, 3), 0),
                array_fill_keys(range(4, 8), 1),
                array_fill_keys(range(9, 15), 2),
                array_fill_keys(range(16, 22), 3),
                array_fill_keys(range(23, 24), 4)
            );

            foreach (range(18, 20) as $i) {
                self::$dsvMatrix[$i] =& $b;
            }

            $c = array_merge(
                array_fill_keys(range(0, 2), 0),
                array_fill_keys(range(3, 5), 1),
                array_fill_keys(range(6, 12), 2),
                array_fill_keys(range(13, 20), 3),
                array_fill_keys(range(21, 24), 4)
            );

            foreach (range(21, 25) as $i) {
                self::$dsvMatrix[$i] =& $c;
            }

            $d = array_merge(
                array_fill_keys(range(0, 3), 0),
                array_fill_keys(range(4, 8), 1),
                array_fill_keys(range(9, 15), 2),
                array_fill_keys(range(16, 22), 3),
                array_fill_keys(range(23, 24), 4)
            );

            foreach (range(26, 29) as $i) {
                self::$dsvMatrix[$i] =& $d;
            }
        }

        return self::$dsvMatrix;
    }

    /**
     * Calculate and return the disease severity value.
     *
     * @return int
     */
    public function calculate()
    {
        if (null === $this->getMeanTemperature() || null === $this->getLeafWettingTime()) {
            throw new \UnexpectedValueException('Both mean temperature and leaf-wetting time must be defined');
        }

        $matrix = self::getDsvMatrix();
        $meanTemperature = (int) $this->getMeanTemperature();
        $leafWettingTime = $this->getLeafWettingTime();

        if (
            array_key_exists($meanTemperature, $matrix) &&
            array_key_exists($leafWettingTime, $matrix[$meanTemperature])
        ) {
            $dsv = $matrix[$meanTemperature][$leafWettingTime];
        } else {
            $dsv = 0;
        }

        return $dsv;
    }

    /**
     * Gets the value of meanTemperature.
     *
     * @return integer
     */
    public function getMeanTemperature()
    {
        return $this->meanTemperature;
    }

    /**
     * Sets the value of meanTemperature.
     *
     * @param float $meanTemperature
     *
     * @return self
     */
    public function setMeanTemperature($meanTemperature)
    {
        if (false === $meanTemperature = filter_var($meanTemperature, FILTER_VALIDATE_FLOAT)) {
            throw new \InvalidArgumentException('Cannot validate mean temperature as a float');
        }

        $this->meanTemperature = $meanTemperature;

        return $this;
    }

    /**
     * Gets the value of leafWettingTime.
     *
     * @return integer
     */
    public function getLeafWettingTime()
    {
        return $this->leafWettingTime;
    }

    /**
     * Sets the value of leafWettingTime.
     *
     * @param integer $leafWettingTime
     *
     * @return self
     */
    public function setLeafWettingTime($leafWettingTime)
    {
        if (false === $leafWettingTime = filter_var($leafWettingTime, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Cannot validate leaf-wetting time as an integer');
        }

        $this->leafWettingTime = $leafWettingTime;

        return $this;
    }
}
