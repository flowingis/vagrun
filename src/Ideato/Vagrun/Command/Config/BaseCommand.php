<?php

namespace Ideato\Vagrun\Command\Config;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class BaseCommand extends Config
{
    /**
     * @var string
     */
    protected $projectName;

    protected function configure()
    {
        $this
            ->setName('config:base')
            ->setDescription('Configure your vagrant machine (basic mode)')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Set path of current working directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this
            ->checkVagrantfileExists()
            ->getProjectName($input, $output)
            ->configureFiles($input, $output)
            ->configureVagrantfile($input, $output)
            ->outputConfiguration($output);
    }

    protected function getProjectName(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $output->writeln("<info>Select the name of the project.\nIt will be used to configure all parameters of your Vagrant machine.</info>");
        $question = '<question>Project name:</question> ';
        $this->projectName = $helper->ask($input, $output, new Question($question));

        //remove whitespaces and transform to lowercase
        $this->projectName = trim(strtolower(str_replace(' ', '', $this->projectName)));

        if (!$this->projectName) {
            throw new \RuntimeException('Project name not provided');
        }

        return $this;
    }

    protected function configureFiles(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->configPaths as $name => $path) {
            if (file_exists($this->currentDir.DIRECTORY_SEPARATOR.$path)) {
                $this->configureFile($input, $output, $name, $path);
            }
        }

        return $this;
    }

    protected function configureVagrantfile(InputInterface $input, OutputInterface $output)
    {
        $vagrantFile = rtrim($this->currentDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'Vagrantfile';
        $vagrantFileContent = file_get_contents($vagrantFile);

        //set the path of vagrantconfig.yml according to Vagrantfile location
        $replacePairs = array(
            'vagrantconfig.yml' => 'vagrant/vagrantconfig.yml',
            'scripts/' => 'vagrant/scripts/',
        );

        $syncedFolder = '/var/www/'.$this->projectName;
        $replacePairs['/var/www'] = $syncedFolder;
        $replacePairs[':args => "/var/www"'] = ':args => "'.$syncedFolder.'/vagrant"';

        $vagrantFileContent = strtr($vagrantFileContent, $replacePairs);
        file_put_contents($vagrantFile, $vagrantFileContent);

        return $this;
    }

    protected function configureFile(InputInterface $input, OutputInterface $output, $name, $path)
    {
        $tpl = __DIR__.'/../../Resources/'.$path;
        $tplContent = file_get_contents($tpl);

        $fileContent = strtr($tplContent, [
            '{{ projectName }}' => $this->projectName,
        ]);

        file_put_contents($this->currentDir.DIRECTORY_SEPARATOR.$path, $fileContent);

        return $this;
    }

    protected function outputConfiguration(OutputInterface $output)
    {
        $output->writeln("\n<info>Vagrant successfully configured!\nNow run `vagrant up` to create your Vagrant machine</info>\n\n");

        $output->writeln('<info>Below you can find some useful settings from your configuration</info>\n');
        $output->writeln(sprintf('<question>Vagrant name</question>: <comment>%s</comment>', $this->projectName));
        $output->writeln(sprintf('<question>Vagrant synced folder</question>: <comment>/var/www/%s</comment>', $this->projectName));
        $output->writeln(sprintf('<question>Apache Document Root</question>: <comment>/var/www/%s</comment>', $this->projectName));
        $output->writeln('<question>PHP Version</question>: <comment>5.6</comment>');
        $output->writeln(sprintf('<question>MySQL user</question>: <comment>%s</comment>', $this->projectName));
        $output->writeln(sprintf('<question>MySQL password</question>: <comment>%s</comment>', $this->projectName));
        $output->writeln(sprintf('<question>MySQL db</question>: <comment>%s</comment>', $this->projectName));
    }
}
