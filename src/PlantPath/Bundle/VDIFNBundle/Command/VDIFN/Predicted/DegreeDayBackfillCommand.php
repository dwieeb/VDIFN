<?php

namespace PlantPath\Bundle\VDIFNBundle\Command\VDIFN\Predicted;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DegreeDayBackfillCommand extends ContainerAwareCommand
{
    /**
     * @var Doctrine\Common\Persistence\ObjectManager
     */
    protected $em;

    /**
     * @var Doctrine\Common\Persistence\ObjectRepository
     */
    protected $dailyRepo;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('vdifn:predicted:degreeday-backfill')
            ->setDescription('Backfills degree days for every predicted daily weather entry.')
            ->addArgument('date', InputArgument::REQUIRED, 'The upper bound date.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('logger');
        $ymd = $input->getArgument('date');
        $date = new \DateTime($ymd);

        $this->em = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager');

        // http://konradpodgorski.com/blog/2013/01/18/how-to-avoid-memory-leaks-in-symfony-2-commands
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        $this->dailyRepo = $this->em->getRepository('PlantPathVDIFNBundle:Weather\Daily');

        $results = $this
            ->dailyRepo
            ->createQueryBuilder('d')
            ->where('d.time < :date')
            ->getQuery()
            ->setParameter('date', $date)
            ->iterate();

        foreach ($results as $row) {
            $day = $row[0];
            $day->calculateDegreeDays();
            $this->em->persist($day);
            $this->em->flush();
            $this->em->clear();
        }
    }
}
