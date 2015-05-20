<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo\Model;

class LateBlightStatus
{
    const NOT_OBSERVED = 'not_observed';
    const ISOLATED_OUTBREAK = 'isolated_outbreak';
    const WIDESPREAD_OUTBREAK = 'widespread_outbreak';

    /**
     * @var array
     */
    public static $validNames = [
        LateBlightStatus::NOT_OBSERVED => 'Not Observed',
        LateBlightStatus::ISOLATED_OUTBREAK => 'Isolated Outbreak',
        LateBlightStatus::WIDESPREAD_OUTBREAK => 'Widespread Outbreak',
    ];

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
            throw new \InvalidArgumentException("$slug is not a valid late blight status slug.");
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
}
