<?php

namespace PlantPath\Bundle\VDIFNBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class VDIFNDownloadCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        // Default date is today.
        $date = new \DateTime();

        $this
            ->setName('vdifn:download')
            ->setDescription('Download a data file from NOAA for a specific day and prediction hour')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Specify a date for which to download NOAA data (Format: Ymd)', $date->format('Ymd'))
            ->addOption('hour', 'p', InputOption::VALUE_REQUIRED, 'Specify a prediction hour for which to download (e.g. 00, 03, 84)', '00');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('logger');
        $ymd = $input->getOption('date');
        $hour = str_pad((string) $input->getOption('hour'), 2, '0', STR_PAD_LEFT);
        $url = sprintf($this->getContainer()->getParameter('vdifn.noaa_url'), $ymd, $hour);
        $filepath = sprintf($this->getContainer()->getParameter('vdifn.noaa_path'), $ymd, $hour);

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

        curl_exec($ch);

        if (200 !== $code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            throw new \RuntimeException('Got HTTP response code ' . $code . ' for URL: ' . $url);
        }

        curl_close($ch);
        fclose($fh);

        $logger->info('Finished download.');
    }
}
