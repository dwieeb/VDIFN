<?php

namespace PlantPath\Bundle\VDIFNBundle\Tests\Geo;

use PlantPath\Bundle\VDIFNBundle\Geo\DegreeDay;

class DegreeDayTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        DegreeDay::create(10.0, 80);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateInvalidBaseTemperature()
    {
        DegreeDay::create("a", 80);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateInvalidAverageDailyTemplerature()
    {
        DegreeDay::create(10.0, "a");
    }

    public function testCalculate()
    {
        $dd = DegreeDay::create(10.0, 80);
        $this->assertEquals($dd->calculate(), 70);
    }

    public function testCalculateZero()
    {
        $dd = DegreeDay::create(10.0, 10);
        $this->assertEquals($dd->calculate(), 0);
    }

    public function testCalculateAverageLessThan()
    {
        $dd = DegreeDay::create(10.0, 0);
        $this->assertEquals($dd->calculate(), 0);
    }
}
