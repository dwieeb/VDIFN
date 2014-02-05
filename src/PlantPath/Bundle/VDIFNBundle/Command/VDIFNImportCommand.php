<?php

namespace PlantPath\Bundle\VDIFNBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use MrRio\ShellWrap as sh;
use MrRio\ShellWrapException as shException;

class VDIFNImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('vdifn:import')
            ->setDescription('Download & import NOAA data into VDIFN');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('vdifn:download');
        $command->run($input, $output);
    }
}
