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
        // Disease::EARLY_BLIGHT => 'Early Blight',
        Disease::LATE_BLIGHT => 'Late Blight',
        // Disease::DOWNY_MILDEW => 'Downy Mildew',
    ];

    /**
     * @var array
     */
    public static $descriptions = [
        Disease::FOLIAR_DISEASE =>
            '<p><u>Alternaria leaf blight</u>: the seedborne <i>Alternaria</i> fungus causes dark-brown lesions on leaflets and petioles that weaken and/or kill carrot foliage, causing separation from root crowns during mechanical harvest.</p>' .
            '<p>Disease management includes using certified or heat-treated seed, crop rotation, in- furrow irrigation to reduce foliar wetness, and disease forecasting programs for initiating a fungicide program.</p>' .
            '<p><u>Cercospora leaf blight</u>: the potentially seedborne <i>Cercospora</i> fungus causes tan lesions with a darker brown margin on carrot leaflets and petioles. Plant growth can be reduced from dead, curled leaflets and, in severe cases, death of the entire canopy.</p>' .
            '<p>Disease management includes using certified or pre-treated seed, crop rotation, avoiding overhead irrigation to reduce foliar wetness, and disease forecasting programs for initiating a fungicide program.</p>' .
            '<p>[<a target="_blank" href="http://www.plantpath.wisc.edu/wivegdis/">http://www.plantpath.wisc.edu/wivegdis/</a>]</p>',
        Disease::LATE_BLIGHT =>
            '<p><u>Late blight</u>: <i>Phytophthora infestans</i> infects all aboveground plant parts and potato tubers and can be transmitted via seed, culls, volunteers, and weeds (i.e., nightshade). Foliar infections begin with watersoaking and progress quickly to cause tan/brown dead tissue. Brown cankers can girdle petioles and stems. White, downy sporulation is often visible, with high humidity, on undersides of leaves along lesion edges. Infected tomato fruits remain firm underneath mottled-looking brown areas. Infected tubers appear as brown decay on the surface and into the top ¼-inch of tissue. Late blight disease advances quickly under conditions of high humidity (&ge;90%) and cool temperatures (50-70&deg;F). Prevention is critical for control. Eliminate culls and volunteer plants. Avoid prolonged wetness on leaves and canopy, use certified seed, and follow DSV accumulation values that prompt early, preventative fungicide applications. If disease is present, treat with appropriate fungicides on a 5-7 day spray interval.</p>' .
            '<p>[<a target="_blank" href="http://www.plantpath.wisc.edu/wivegdis/">http://www.plantpath.wisc.edu/wivegdis/</a>]</p>',
        Disease::EARLY_BLIGHT =>
            '<p><u>Early blight: The fungus Alternaria solani typically infects leaves in the lower canopy where airflow is limited, humidity is high, and leaf wetness is prolonged. As dark brown lesions enlarge, they develop concentric rings accompanied by a yellow halo around the dark spots. Entire leaves may become diseased and die with a potential for defoliation. Prevent disease by using resistant plant varieties, if possible. Avoid foliar moisture/humidity by increasing plant spacing. Destroy and remove infected plant material to avoid carryover of infested plant debris the following year, which is a source of inoculum. Use P-DAY disease forecasting system for initiating preventative fungicides.</p>',
    ];

    /**
     * @var array
     */
    public static $cropMapping = [
        Crop::CARROT => [Disease::FOLIAR_DISEASE],
        Crop::POTATO => [/* Disease::EARLY_BLIGHT, */ Disease::LATE_BLIGHT],
        Crop::HOPS => [/* Disease::DOWNY_MILDEW */],
    ];
}
