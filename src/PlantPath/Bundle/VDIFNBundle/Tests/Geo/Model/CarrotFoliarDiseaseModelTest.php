<?php

namespace PlantPath\Bundle\VDIFNBundle\Tests\Geo\Model;

use PlantPath\Bundle\VDIFNBundle\Geo\Threshold;
use PlantPath\Bundle\VDIFNBundle\Geo\Model\DiseaseModelData;
use PlantPath\Bundle\VDIFNBundle\Geo\Model\CarrotFoliarDiseaseModel;

class CarrotFoliarDiseaseModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage mean temperature
     */
    public function testApplyInvalidMeanTemperature()
    {
        CarrotFoliarDiseaseModel::apply('daniel', 0);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage leaf-wetting time
     */
    public function testApplyInvalidLeafWettingTime()
    {
        CarrotFoliarDiseaseModel::apply(1, 'abc');
    }

    public function testApply()
    {
        $this->assertEquals(0, CarrotFoliarDiseaseModel::apply(-10, 0));
        $this->assertEquals(0, CarrotFoliarDiseaseModel::apply(0, 25));
        $this->assertEquals(0, CarrotFoliarDiseaseModel::apply(0, 0));
        $this->assertEquals(1, CarrotFoliarDiseaseModel::apply(15.4, 10));
        $this->assertEquals(2, CarrotFoliarDiseaseModel::apply(18, 10));
        $this->assertEquals(3, CarrotFoliarDiseaseModel::apply(23.6, 15));
        $this->assertEquals(4, CarrotFoliarDiseaseModel::apply(27.2, 23));
    }

    public function testDetermineThreshold()
    {
        $data = new DiseaseModelData();
        $data->setDayTotal(2);
        $this->assertEquals(Threshold::VERY_LOW, CarrotFoliarDiseaseModel::determineThreshold($data));
        $data->setDayTotal(7);
        $this->assertEquals(Threshold::LOW, CarrotFoliarDiseaseModel::determineThreshold($data));
        $data->setDayTotal(12);
        $this->assertEquals(Threshold::MEDIUM, CarrotFoliarDiseaseModel::determineThreshold($data));
        $data->setDayTotal(17);
        $this->assertEquals(Threshold::HIGH, CarrotFoliarDiseaseModel::determineThreshold($data));
        $data->setDayTotal(22);
        $this->assertEquals(Threshold::VERY_HIGH, CarrotFoliarDiseaseModel::determineThreshold($data));
    }
}
