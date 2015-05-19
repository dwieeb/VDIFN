<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo;

class Threshold
{
    const VERY_HIGH = 'very_high';
    const HIGH = 'high';
    const MEDIUM = 'medium';
    const LOW = 'low';
    const VERY_LOW = 'very_low';

    /**
     * @var array
     */
    public static $validNames = [
        Threshold::VERY_HIGH => 'Very High',
        Threshold::HIGH => 'High',
        Threshold::MEDIUM => 'Medium',
        Threshold::LOW => 'Low',
        Threshold::VERY_LOW => 'Very Low',
    ];

    /**
     * @var array
     */
    public static $cmpHelper = [
        Threshold::VERY_HIGH => 'e',
        Threshold::HIGH => 'd',
        Threshold::MEDIUM => 'c',
        Threshold::LOW => 'b',
        Threshold::VERY_LOW => 'a',
    ];

    /**
     * Threshold comparison.
     *
     * @param string Slug for first threshold.
     * @param string Slug for second threshold.
     *
     * @return integer
     */
    public static function cmp($threshold1, $threshold2)
    {
        if (!static::isValidSlug($threshold1)) {
            throw new \InvalidArgumentException("$threshold1 is not a valid threshold slug.");
        }

        if (!static::isValidSlug($threshold2)) {
            throw new \InvalidArgumentException("$threshold2 is not a valid threshold slug.");
        }

        return strcmp(static::$cmpHelper[$threshold1], static::$cmpHelper[$threshold2]);
    }

    /**
     * Get the valid threshold slugs and names.
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
            throw new \InvalidArgumentException("$slug is not a valid threshold slug.");
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
     * Returns an array in the format that a Symfony form can understand.
     *
     * @return array
     */
    public static function getFormChoices()
    {
        return static::getValidNames();
    }
}
