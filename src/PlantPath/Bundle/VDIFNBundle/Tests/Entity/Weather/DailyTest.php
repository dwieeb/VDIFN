<?php

namespace PlantPath\Bundle\VDIFNBundle\Tests\Entity\Weather;

use PlantPath\Bundle\VDIFNBundle\Entity\Weather\Daily;

class DailyTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $daily = new Daily();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCalculateDegreeDaysEmptyMeanTemperature()
    {
        $daily = new Daily();
        $daily->calculateDegreeDays();
    }

    public function testCalculateDegreeDays()
    {
        $daily = new Daily();
        $daily->setMeanTemperature(80);
        $daily->calculateDegreeDays();
        $this->assertEquals($daily->getDegreeDay10(), 70);
        $this->assertEquals($daily->getDegreeDay72(), 72.8888, '', 0.2);
        $this->assertEquals($daily->getDegreeDay44(), 75.6666, '', 0.2);
        $this->assertEquals($daily->getDegreeDay27(), 77.2222, '', 0.2);
    }

    public function testCalculateDegreeDaysMiddle()
    {
        $daily = new Daily();
        $daily->setMeanTemperature(5);
        $daily->calculateDegreeDays();
        $this->assertEquals($daily->getDegreeDay10(), 0);
        $this->assertEquals($daily->getDegreeDay72(), 0);
        $this->assertEquals($daily->getDegreeDay44(), 0.6666, '', 0.2);
        $this->assertEquals($daily->getDegreeDay27(), 2.3333, '', 0.2);
    }
}
