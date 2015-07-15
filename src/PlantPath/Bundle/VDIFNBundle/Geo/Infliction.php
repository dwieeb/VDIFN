<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo;

class Infliction
{
    /**
     * Get the valid infliction slugs and names.
     *
     * @return array
     */
    public static function getCropMapping()
    {
        $diseases = Disease::getCropMapping();
        $pests = Pest::getCropMapping();
        $cropMapping = [];

        foreach (Crop::getValidNames() as $slug => $name) {
            if (!empty($diseases[$slug])) {
                $cropMapping[$slug]['diseases'] = $diseases[$slug];
            }

            if (!empty($pests[$slug])) {
                $cropMapping[$slug]['pests'] = $pests[$slug];
            }
        }

        return $cropMapping;
    }

    /**
     * Returns an array in the format that a Symfony form can understand.
     *
     * @return array
     */
    public static function getFormChoices()
    {
        $choices = [];

        foreach (Disease::getCropMapping() as $slug => $diseases) {
            foreach ($diseases as $diseaseSlug) {
                $key = Crop::getNameBySlug($slug) . ' Diseases';

                if (!isset($choices[$key])) {
                    $choices[$key] = [];
                }

                $choices[$key][$diseaseSlug] = Disease::getNameBySlug($diseaseSlug);
            }
        }

        foreach (Pest::getCropMapping() as $slug => $pests) {
            foreach ($pests as $pestSlug) {
                $key = Crop::getNameBySlug($slug) . ' Pests';

                if (!isset($choices[$key])) {
                    $choices[$key] = [];
                }

                $choices[$key][$pestSlug] = Pest::getNameBySlug($pestSlug);
            }
        }

        ksort($choices);

        return $choices;
    }

    /**
     * Returns a map of infliction IDs and their corresponding description.
     *
     * @return array
     */
    public static function getDescriptions()
    {
        $map = [];

        foreach (Disease::getCropMapping() as $slug => $diseases) {
            foreach ($diseases as $diseaseSlug) {
                $description = Disease::getDescriptionBySlug($diseaseSlug);

                if (null !== $description) {
                    $map[$diseaseSlug] = $description;
                }
            }
        }

        foreach (Pest::getCropMapping() as $slug => $pests) {
            foreach ($pests as $pestSlug) {
                $description = Pest::getDescriptionBySlug($pestSlug);

                if (null !== $description) {
                    $map[$pestSlug] = $description;
                }
            }
        }

        return $map;
    }

    /**
     * Delegate to pest or disease isValidSlug().
     *
     * @param string $slug
     */
    public static function isValidSlug($slug)
    {
        $class = AbstractInfliction::getClassBySlug($slug);

        return $class::isValidSlug($slug);
    }

    /**
     * Delegate to pest or disease getNameBySlug().
     *
     * @param string $slug
     */
    public static function getNameBySlug($slug)
    {
        $class = AbstractInfliction::getClassBySlug($slug);

        return $class::getNameBySlug($slug);
    }
}
