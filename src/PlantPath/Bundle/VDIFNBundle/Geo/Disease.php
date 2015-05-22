<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo;

class Disease
{
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

    /**
     * Get the valid disease names.
     *
     * @return array
     */
    public static function getValidNames()
    {
        return static::$validNames;
    }

    /**
     * Return a pretty name by a slug.
     *
     * @return string
     */
    public static function getNameBySlug($slug)
    {
        if (!static::isValidSlug($slug)) {
            throw new \InvalidArgumentException("$slug is not a valid disease slug.");
        }

        return static::getValidNames()[$slug];
    }

    /**
     * Determines whether a slug is valid or not.
     *
     * @param string $slug
     */
    public static function isValidSlug($slug)
    {
        return in_array($slug, array_keys(static::$validNames));
    }

    /**
     * Get the crop/disease mapping.
     *
     * @return array
     */
    public static function getCropMapping()
    {
        return static::$cropMapping;
    }

    /**
     * Determine the database DSV column name for a given crop and disease.
     *
     * @param string $crop
     * @param string $disease
     */
    public static function getDsvFieldName($crop, $disease)
    {
        if (!Crop::isValidSlug($crop)) {
            throw new \InvalidArgumentException("$crop is not a valid crop slug.");
        }

        if (!static::isValidSlug($disease)) {
            throw new \InvalidArgumentException("$disease is not a valid disease slug.");
        }

        $disease = substr($disease, 8);

        return 'dsv' . ucfirst($crop) . str_replace(' ', '', ucwords(str_replace('-', ' ', $disease)));
    }
}
