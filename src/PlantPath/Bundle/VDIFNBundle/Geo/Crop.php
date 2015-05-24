<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo;

class Crop
{
    const CARROT = 'carrot';
    const ONION = 'onion';
    const POTATO = 'potato';
    const HOPS = 'hops';

    /**
     * @var array
     */
    public static $validNames = [
        Crop::CARROT => 'Carrot',
        // Crop::ONION => 'Onion',
        Crop::POTATO => 'Potato',
        // Crop::HOPS => 'Hops',
    ];

    /**
     * @var string
     */
    protected $slug;

    /**
     * Constructor.
     *
     * @param string $slug
     */
    public function __construct($slug)
    {
        $this->setSlug($slug);
    }

    /**
     * Return the valid crop slugs and names.
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
            throw new \InvalidArgumentException("$slug is not a valid crop slug.");
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

    /**
     * Get slug.
     *
     * @return slug.
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set slug.
     *
     * @param slug the value to set.
     */
    public function setSlug($slug)
    {
        if (!static::isValidSlug($slug)) {
            throw new \InvalidArgumentException("$slug is not a valid crop slug.");
        }

        $this->slug = $slug;

        return $this;
    }
}
