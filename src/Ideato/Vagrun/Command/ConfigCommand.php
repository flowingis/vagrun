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
            ->setHelp("By default the command will ask you just the project name and it automatically will configure
all parameters with your project name. (Eg. virtual host, db name, db pass, etc.)\n
With --verbose option there will be prompted each single parameter and you can configure them one by one.")
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Set path of current working directory');
    }

    /**
     * This command is just a proxy for more specific command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $verbose = $input->getOption('verbose');
        $commandName = $verbose ? 'config:verbose' : 'config:base';

        $command = $this->getApplication()->find($commandName);
        $command->run($input, $output);
    }
}
