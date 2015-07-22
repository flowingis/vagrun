<?php

namespace Ideato\Vagrun\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('config')
            ->setDescription('Configure your vagrant machine')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Set path of current working directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('config:verbose');
        $command->run($input, $output);
    }
}
