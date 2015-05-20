<?php

namespace PlantPath\Bundle\VDIFNBundle\Tests\Geo\Model;

use PlantPath\Bundle\VDIFNBundle\Geo\Threshold;
use PlantPath\Bundle\VDIFNBundle\Geo\Model\DiseaseModelData;
use PlantPath\Bundle\VDIFNBundle\Geo\Model\LateBlightDiseaseModel;

class LateBlightDiseaseModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage mean temperature
     */
    public function testApplyInvalidMeanTemperature()
    {
        LateBlightDiseaseModel::apply('daniel', 0);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage leaf-wetting time
     */
    public function testApplyInvalidLeafWettingTime()
    {
        LateBlightDiseaseModel::apply(1, 'abc');
    }

    public function testApply()
    {
        $this->assertEquals(0, LateBlightDiseaseModel::apply(5, 17));
        $this->assertEquals(0, LateBlightDiseaseModel::apply(8, 25));
        $this->assertEquals(0, LateBlightDiseaseModel::apply(8, 8));
        $this->assertEquals(0, LateBlightDiseaseModel::apply(13, 6));
        $this->assertEquals(0, LateBlightDiseaseModel::apply(18, 5));
        $this->assertEquals(1, LateBlightDiseaseModel::apply(8, 17));
        $this->assertEquals(1, LateBlightDiseaseModel::apply(13.1, 14));
        $this->assertEquals(1, LateBlightDiseaseModel::apply(19, 11));
        $this->assertEquals(2, LateBlightDiseaseModel::apply(8, 20));
        $this->assertEquals(2, LateBlightDiseaseModel::apply(13, 17));
        $this->assertEquals(2, LateBlightDiseaseModel::apply(25.3, 14));
        $this->assertEquals(3, LateBlightDiseaseModel::apply(9, 23));
        $this->assertEquals(3, LateBlightDiseaseModel::apply(14, 20));
        $this->assertEquals(3, LateBlightDiseaseModel::apply(26.5, 18));
        $this->assertEquals(4, LateBlightDiseaseModel::apply(13, 23));
        $this->assertEquals(4, LateBlightDiseaseModel::apply(15.6, 20));
    }
}
