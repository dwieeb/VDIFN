<?php

namespace PlantPath\Bundle\VDIFNBundle\EventListener;

use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Psr\Log\LoggerInterface;

class ConsoleExceptionListener
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onConsoleException(ConsoleExceptionEvent $event)
    {
        $container = $event->getCommand()->getApplication()->getKernel()->getContainer();
        $raven = $container->get('raven');
        $raven->captureException($event->getException());
    }
}
