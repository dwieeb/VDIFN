<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo;

class Disease extends AbstractInfliction
{
    const INFLICTION_TYPE = 'disease';

    const FOLIAR_DISEASE = 'disease-foliar-disease';
    const EARLY_BLIGHT = 'disease-early-blight';
    const LATE_BLIGHT = 'disease-late-blight';
    const DOWNY_MILDEW = 'disease-downy-mildew';

    /**
     * @var array
     */
    public static $validNames = [
        Disease::FOLIAR_DISEASE => 'Foliar Disease',
        Disease::EARLY_BLIGHT => 'Early Blight',
        Disease::LATE_BLIGHT => 'Late Blight',
        Disease::DOWNY_MILDEW => 'Downy Mildew',
    ];

    /**
     * @var array
     */
    public static $cropMapping = [
        Crop::CARROT => [Disease::FOLIAR_DISEASE],
        Crop::POTATO => [Disease::EARLY_BLIGHT, Disease::LATE_BLIGHT],
        Crop::HOPS => [Disease::DOWNY_MILDEW],
    ];
}
