<?php

namespace Ideato\Vagrun\Command\Config;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

abstract class Config extends Command
{
    protected $currentDir;

    /** @var Filesystem */
    protected $fs;

    /** @var OutputInterface */
    protected $output;

    /** @var Array */
    protected $configPaths = array(
        'vagrantconfig' => 'vagrant/vagrantconfig.yml',
        'webserver' => 'vagrant/provisioning/ideato.webserver/vars/main.yml',
        'database' => 'vagrant/provisioning/ideato.database.mysql/vars/main.yml',
    );

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->fs = new Filesystem();

        $this->currentDir = getcwd().DIRECTORY_SEPARATOR;

        if ($input->getOption('path')) {
            $this->currentDir = $input->getOption('path').DIRECTORY_SEPARATOR;
        }
    }

    protected function checkVagrantfileExists()
    {
        $vagrantFile = rtrim($this->currentDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'Vagrantfile';

        if (!$this->fs->exists($vagrantFile)) {
            throw new \RuntimeException('Vagrant template is not initialized. Please run `vagrun init` instead.');
        }

        return $this;
    }

    protected function createQuestion($question, $default)
    {
        $question = sprintf('<question>%s</question> (<comment>%s</comment>): ', $question, $default);

        return new Question($question, $default);
    }

    abstract protected function configureFiles(InputInterface $input, OutputInterface $output);

    abstract protected function configureVagrantfile(InputInterface $input, OutputInterface $output);

}