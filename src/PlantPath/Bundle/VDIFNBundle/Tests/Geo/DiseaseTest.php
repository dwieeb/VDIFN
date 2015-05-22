<?php

namespace PlantPath\Bundle\VDIFNBundle\Tests\Geo;

use PlantPath\Bundle\VDIFNBundle\Geo\Crop;
use PlantPath\Bundle\VDIFNBundle\Geo\Disease;

class InflictionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDsvColumnName()
    {
        $this->assertEquals('dsvCarrotFoliarDisease', Disease::getDsvFieldName(Crop::CARROT, Disease::FOLIAR_DISEASE));
        $this->assertEquals('dsvPotatoLateBlight', Disease::getDsvFieldName(Crop::POTATO, Disease::LATE_BLIGHT));
    }
}
