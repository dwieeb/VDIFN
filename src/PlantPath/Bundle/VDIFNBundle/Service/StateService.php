<?php

namespace PlantPath\Bundle\VDIFNBundle\Service;

use PlantPath\Bundle\VDIFNBundle\Geo\Point;
use PlantPath\Bundle\VDIFNBundle\Geo\PointCollection;
use PlantPath\Bundle\VDIFNBundle\Geo\State;
use Symfony\Component\DomCrawler\Crawler;

class StateService
{
    /**
     * The DOMElement loaded from the XML file.
     *
     * @var \DOMElement
     */
    protected $dom;

    /**
     * An array of cached state objects.
     *
     * @var array
     */
    protected $states = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dom = new \DOMDocument();
        $this->dom->load(__DIR__.'/../Resources/config/states.xml');
    }

    /**
     * Returns a new State object with latitudinal/longitudinal boundaries.
     *
     * @param  string $name
     *
     * @return PlantPath\Bundle\VDIFNBundle\Geo\State
     */
    public function getStateWithBoundaries($name)
    {
        if (!array_key_exists($name, $this->states)) {
            $crawler = new Crawler($this->dom->getElementsByTagName('state'));
            $state = $crawler->filter('state[name="' . $name . '"]')->eq(0);

            $points = new PointCollection();

            foreach ($state->children() as $point) {
                $points->addPoint(new Point($point->getAttribute('lat'), $point->getAttribute('lng')));
            }

            $this->states[$name] = new State($name, $points);
        }

        return $this->states[$name];
    }
}
