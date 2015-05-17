<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo;

class Pest
{
    const ASTER_LEAFHOPPER = 'pest-aster-leafhopper';
    const CARROT_WEEVIL = 'pest-carrot-weevil';
    const CARROT_RUST_FLY = 'pest-carrot-rust-fly';
    const ONION_MAGGOT = 'pest-onion-maggot';
    const COLORADO_POTATO_BEETLE = 'pest-colorado-potato-beetle';
    const POTATO_LEAFHOPPER = 'pest-potato-leafhopper';

    /**
     * @var array
     */
    public static $validNames = [
        Pest::ASTER_LEAFHOPPER => 'Aster Leafhopper',
        Pest::CARROT_WEEVIL => 'Carrot Weevil',
        Pest::CARROT_RUST_FLY => 'Carrot Rust Fly',
        Pest::ONION_MAGGOT => 'Onion Maggot',
        Pest::COLORADO_POTATO_BEETLE => 'Colorado Potato Beetle',
        Pest::POTATO_LEAFHOPPER => 'Potato Leafhopper',
    ];

    /**
     * @var array
     */
    public static $cropMapping = [
        Crop::CARROT => [Pest::ASTER_LEAFHOPPER, Pest::CARROT_WEEVIL, Pest::CARROT_RUST_FLY],
        Crop::ONION => [Pest::ONION_MAGGOT],
        Crop::POTATO => [Pest::COLORADO_POTATO_BEETLE, Pest::POTATO_LEAFHOPPER, Pest::ASTER_LEAFHOPPER]
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
            throw new \InvalidArgumentException("$slug is not a valid pest slug.");
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
     * Get the crop/pest mapping.
     *
     * @return array
     */
    public static function getCropMapping()
    {
        return static::$cropMapping;
    }
}
