<?php

namespace PlantPath\Bundle\VDIFNBundle\Command\VDIFN\Observed;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        // Default date is today.
        $date = new \DateTime();

        $this
            ->setName('vdifn:observed:download')
            ->setDescription('Download a data file from NOAA for a specific year')
            ->addArgument('usaf', InputArgument::REQUIRED, 'The USAF value that identifies the station')
            ->addArgument('wban', InputArgument::REQUIRED, 'The WBAN value that identifies the station')
            ->addOption('year', 'y', InputOption::VALUE_REQUIRED, 'Specify the year to download (Format: Y)', $date->format('Y'));
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('logger');
        $usaf = $input->getArgument('usaf');
        $wban = $input->getArgument('wban');
        $year = $input->getOption('year');
        $url = sprintf($this->getContainer()->getParameter('vdifn.noaa.observed.url.data_file'), $year, $usaf, $wban, $year);
        $filepath = sprintf($this->getContainer()->getParameter('vdifn.noaa.observed.path.data_file'), $year, $usaf, $wban);

        $logger->info('Starting download.', ['url' => $url, 'filepath' => $filepath]);

        // Make directory recursively if it does not already exist. 0777 runs through umask
        if (!file_exists(dirname($filepath)) && false === mkdir(dirname($filepath), 0777, true)) {
            throw new \RuntimeException('Unable to make cache directory: ' . dirname($filepath));
        }

        if (false === $fh = fopen($filepath, 'w')) {
            throw new \RuntimeException('Unable to open file handler at: ' . $filepath);
        }

        $ch = curl_init($url);

        // Write the file to disk as it downloads.
        curl_setopt($ch, CURLOPT_FILE, $fh);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);

        curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);

        if ($curl_errno > 0) {
            if ($curl_errno === 78) {
                return 1;
            }

            throw new \RuntimeException('cURL Error (' . $curl_errno . '): ' . $curl_error);
        }

        if (226 !== curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            throw new \RuntimeException('Got HTTP response code ' . $code . ' for URL: ' . $url);
        }

        curl_close($ch);
        fclose($fh);

        $logger->info('Finished download.');
    }
}
