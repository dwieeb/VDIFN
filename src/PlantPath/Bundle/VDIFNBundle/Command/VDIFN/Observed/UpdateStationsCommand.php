<?php

namespace PlantPath\Bundle\VDIFNBundle\Command\VDIFN\Observed;

use PlantPath\Bundle\VDIFNBundle\Entity\Station;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateStationsCommand extends ContainerAwareCommand
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Doctrine\Common\Persistence\ObjectManager
     */
    protected $em;

    /**
     * @var Doctrine\Common\Persistence\ObjectRepository
     */
    protected $repo;

    protected $logger;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        // Default date is today.
        $date = new \DateTime();

        $this
            ->setName('vdifn:observed:update-stations')
            ->setDescription('Download a data file from NOAA for a specific day of observed weather');
    }

    /**
     * Return the last modified date of the ish-history.csv file on the remote
     * server.
     *
     * @return DateTime
     */
    public function getRemoteLastModified()
    {
        $url = $this->getContainer()->getParameter('vdifn.noaa.observed.url.history_file');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FILETIME, true);
        curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);

        if ($curl_errno > 0) {
            throw new \RuntimeException('cURL Error (' . $curl_errno . '): ' . $curl_error);
        }

        $time = curl_getinfo($ch, CURLINFO_FILETIME);
        curl_close($ch);

        return \DateTime::createFromFormat('U', $time);
    }

    /**
     * Return the last modified date of the local ish-history.csv file.
     *
     * @return DateTime
     *
     * @throws RuntimeException if unable to determine modified time (most
     *                          likely if the file does not exist)
     */
    public function getLocalLastModified()
    {
        if (false === file_exists($this->filepath)) {
            throw new \RuntimeException('File does not exist at: ' . $this->filepath);
        }

        if (false === $time = filemtime($this->filepath)) {
            throw new \RuntimeException('Unable to determine last modified time for file at: ' . $this->filepath);
        }

        return \DateTime::createFromFormat('U', $time);
    }

    /**
     * Basically, run the vdifn:observed:download-stations command.
     */
    protected function downloadHistoryFile()
    {
        $this->logger->info('Downloading a new history file.');
        $console = $this->getApplication();
        $console->find('vdifn:observed:download-stations')->run(new ArrayInput([
            'command' => 'vdifn:observed:download-stations',
        ]), $this->output);
    }

    /**
     * @return boolean true if local history file is up-to-date
     *                 false if local history file is out-of-date
     */
    protected function isLocalHistoryFileCurrent()
    {
        $this->logger->info('Getting remote last modified date.');
        $remoteDate = $this->getRemoteLastModified();
        $this->logger->info('Remote last modified date obtained.', ['date' => $remoteDate]);

        try {
            $this->logger->info('Getting local last modified date.');
            $localDate = $this->getLocalLastModified();
        } catch (\RuntimeException $ex) {
            // There is no local history file, or it has an error.
            return false;
        }

        $this->logger->info('Local last modified date obtained.', ['date' => $localDate]);

        return $remoteDate < $localDate;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->logger = $this->getContainer()->get('logger');
        $this->filepath = $this->getContainer()->getParameter('vdifn.noaa.observed.path.history_file');
        $this->logger->info('Starting update.');
        $startTime = new \DateTime();

        if ($this->isLocalHistoryFileCurrent()) {
            $this->logger->info('Stations up-to-date. Exiting.');
        } else {
            $this->logger->info('Outdated local history file.');
            $this->downloadHistoryFile();

            $this->em = $this
                ->getContainer()
                ->get('doctrine.orm.entity_manager');

            // http://konradpodgorski.com/blog/2013/01/18/how-to-avoid-memory-leaks-in-symfony-2-commands
            $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

            $this->repo = $this->em->getRepository('PlantPathVDIFNBundle:Station');

            $batchSize = 25;

            $this->logger->info('Starting station import.', ['batchSize' => $batchSize, 'filepath' => $this->filepath]);

            $file = new \SplFileObject($this->filepath);
            $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
            $keys = $file->current();

            foreach (new \LimitIterator($file, 1) as $i => $row) {
                if (false !== $row) {
                    $parameters = array_combine($keys, $row);

                    if (null === $station = $this->repo->findOneBy(['usaf' => $parameters['USAF'], 'wban' => $parameters['WBAN']])) {
                        $station = Station::createFromParameters($parameters);
                    }

                    $this->em->persist($station);
                }

                if (($i % $batchSize) === 0) {
                    $this->em->flush();
                    $this->em->clear();
                }
            }

            $this->logger->info('Finished update.');

            $body = $this->getContainer()->get('templating')->render('PlantPathVDIFNBundle:Command:DailyImport/finished.html.twig', [
                'time' => $startTime->diff(new \DateTime()),
            ]);

            $message = \Swift_Message::newInstance()
                ->setSubject('VDIFN Update Stations Finished')
                ->setFrom($this->getContainer()->getParameter('vdifn.admin.email'))
                ->setTo($this->getContainer()->getParameter('vdifn.admin.emails'))
                ->setBody($body, 'text/html');

            $this->getContainer()->get('mailer')->send($message);
        }
    }
}
