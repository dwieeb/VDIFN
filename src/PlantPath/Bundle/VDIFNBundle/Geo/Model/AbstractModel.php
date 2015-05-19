<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo\Model;

use PlantPath\Bundle\VDIFNBundle\Geo\Crop;
use PlantPath\Bundle\VDIFNBundle\Geo\Disease;
use PlantPath\Bundle\VDIFNBundle\Geo\Pest;

abstract class AbstractModel
{
    public static function getModelClassByCropAndInfliction($crop, $infliction)
    {
        switch ($crop) {
        case Crop::CARROT:
            switch ($infliction) {
            case Disease::FOLIAR_DISEASE:
                return 'PlantPath\Bundle\VDIFNBundle\Geo\Model\CarrotFoliarDiseaseModel';
            }

            break;
        case Crop::POTATO:
            switch ($infliction) {
            // case Disease::EARLY_BLIGHT:
            //     return; // Early Blight Model
            case Disease::LATE_BLIGHT:
                return; // Late Blight Model
            }

            break;
        }

        throw new \InvalidArgumentException("A model does not exist by crop: $crop and infliction: $infliction.");
    }
}
