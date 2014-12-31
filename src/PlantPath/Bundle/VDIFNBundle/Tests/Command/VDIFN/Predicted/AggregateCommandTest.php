<?php

namespace PlantPath\Bundle\VDIFNBundle\Tests\Command\VDIFN\Predicted;

use PlantPath\Bundle\VDIFNBundle\Entity\Weather\Hourly as HourlyWeather;
use PlantPath\Bundle\VDIFNBundle\Geo\DateUtils;
use PlantPath\Bundle\VDIFNBundle\Geo\Point;
use PlantPath\Bundle\VDIFNBundle\Command\VDIFN\Predicted\AggregateCommand;

class AggregateCommandTest extends \PHPUnit_Framework_TestCase
{
    public static $start;
    public static $end;
    public static $point;

    public static function setUpBeforeClass()
    {
        self::$start = new \DateTime('2014-12-29');
        self::$start->setTimezone(new \DateTimeZone('UTC'));
        self::$start = DateUtils::getBeginningOfDay(self::$start);
        self::$end = DateUtils::getEndOfDay(self::$start);
        self::$point = new Point(1.0, 1.0);
    }

    protected function mockGetHourlyWeather()
    {
        $hourlies = [];
        $temp = 70;
        $i = 0;

        foreach (new \DatePeriod(self::$start, \DateInterval::createFromDateString('3 hours'), self::$end->modify('+1 second')) as $dt) {
            $i++;

            if ($i < 3) {
                $temp += 10;
            } else {
                $temp -= 5;
            }

            $hourly = HourlyWeather::create()
                ->setReferenceTime(self::$start)
                ->setVerificationTime($dt)
                ->setLatitude(self::$point->getLatitude())
                ->setLongitude(self::$point->getLongitude())
                ->setTemperature($temp)
                ->setRelativeHumidity(75);
            $hourlies[$dt->format('c')] = $hourly;
        }

        // array_pop($hourlies);

        return $hourlies;
    }

    public function testGetInterpolatedHourlyWeather()
    {
        $method = new \ReflectionMethod('PlantPath\Bundle\VDIFNBundle\Command\VDIFN\Predicted\AggregateCommand', 'getInterpolatedHourlyWeather');
        $method->setAccessible(true);
        $cmd = $this->getMockBuilder('PlantPath\Bundle\VDIFNBundle\Command\VDIFN\Predicted\AggregateCommand')
            ->disableOriginalConstructor()
            ->setMethods(['getHourlyWeather'])
            ->getMock();
        $cmd->method('getHourlyWeather')
            ->willReturn($this->mockGetHourlyWeather());
        $method->invoke($cmd, self::$point, self::$start);
    }
}
