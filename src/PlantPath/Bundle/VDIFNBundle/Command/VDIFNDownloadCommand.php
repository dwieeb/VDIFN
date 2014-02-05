<?php

namespace PlantPath\Bundle\VDIFNBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class VDIFNDownloadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        // Default date is today.
        $date = new \DateTime();

        $this
            ->setName('vdifn:download')
            ->setDescription('Download the data from NOAA')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Specify a date for which to download NOAA data (Format: Ymd)', $date->format('Ymd'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ymd = $input->getOption('date');
        $url = sprintf($this->getContainer()->getParameter('vdifn.noaa_url'), $ymd);
        $filepath = $this->getContainer()->getParameter('vdifn.cache_dir') . '/' . $ymd . '/' . substr($url, strrpos($url, '/') + 1);

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
        curl_close($ch);

        fclose($fh);
    }
}
