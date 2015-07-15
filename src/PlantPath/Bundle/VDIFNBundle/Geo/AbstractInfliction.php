<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo;

abstract class AbstractInfliction
{
    /**
     * @var array
     */
    public static $validNames;

    /**
     * @var array
     */
    public static $cropMapping;

    /**
     * Get the class of infliction by its slug.
     */
    public static function getClassBySlug($slug)
    {
        if (0 === strpos($slug, 'disease-')) {
            return 'PlantPath\Bundle\VDIFNBundle\Geo\Disease';
        } else if (0 === strpos($slug, 'pest-')) {
            return 'PlantPath\Bundle\VDIFNBundle\Geo\Pest';
        }

        throw new \InvalidArgumentException("Unknown infliction type.");
    }

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
     * Get the disease descriptions.
     *
     * @return array
     */
    public static function getDescriptions()
    {
        return static::$descriptions;
    }

    /**
     * Return a pretty name by a slug.
     *
     * @return string
     */
    public static function getNameBySlug($slug)
    {
        if (!static::isValidSlug($slug)) {
            throw new \InvalidArgumentException("$slug is not a valid infliction slug.");
        }

        return static::getValidNames()[$slug];
    }

    /**
     * Return a description by a slug.
     *
     * @return string
     */
    public static function getDescriptionBySlug($slug)
    {
        if (!static::isValidSlug($slug)) {
            throw new \InvalidArgumentException("$slug is not a valid infliction slug.");
        }

        $descriptions = static::getDescriptions();

        if (empty($descriptions[$slug])) {
            return null;
        }

        return $descriptions[$slug];
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
     * Get the crop/pest mapping.
     *
     * @return array
     */
    public static function getCropMapping()
    {
        return static::$cropMapping;
    }
}
