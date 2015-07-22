<?php

namespace Ideato\Vagrun\Command\Config;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class VerboseCommand extends Config
{
    protected function configure()
    {
        $this
            ->setName('config:verbose')
            ->setDescription('Configure your vagrant machine with all configuration parameters')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Set path of current working directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this
            ->checkVagrantfileExists()
            ->configureFiles($input, $output)
            ->configureVagrantfile($input, $output)
        ;
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

        $helper = $this->getHelper('question');

        //set the base box name
        if(!preg_match("/config.vm.box = \"(.*)\"/", $vagrantFileContent, $matches)) {
            throw new \RuntimeException('Base box not found in Vagrantfile');
        }
        $defaultBaseBox = $matches[1];
        $question = sprintf('<question>Enter the base box</question> (<comment>%s</comment>): ', $defaultBaseBox);
        $baseBox = $helper->ask($input, $output, new Question($question, $defaultBaseBox));

        //set the path of vagrantconfig.yml according to Vagrantfile location
        $replacePairs = array(
            'vagrantconfig.yml' => 'vagrant/vagrantconfig.yml',
            'scripts/' => 'vagrant/scripts/',
        );

        //ask for synced folder path
        $defaultSyncedFolder = '/var/www';
        $question = sprintf('<question>Enter the synced folder path</question> (<comment>%s</comment>): ', $defaultSyncedFolder);
        $syncedFolder = $helper->ask($input, $output, new Question($question, $defaultSyncedFolder));

        //update Vagrantfile
        if($defaultBaseBox != $baseBox) {
            $replacePairs[$defaultBaseBox] = $baseBox;
        }

        if ($syncedFolder != $defaultSyncedFolder) {
            $replacePairs[$defaultSyncedFolder] = $syncedFolder;
            $replacePairs[':args => "'.$defaultSyncedFolder.'"'] = ':args => "'.$syncedFolder.'/vagrant"';
        }

        $vagrantFileContent = strtr($vagrantFileContent, $replacePairs);
        file_put_contents($vagrantFile, $vagrantFileContent);

        $output->writeln("\n<info>Base box: $baseBox</info>");
        $output->writeln("\n<info>Synced folder: $syncedFolder</info>");
        $output->writeln("\n<info>Vagrantfile sucessfully updated</info>");

        return $this;
    }

    protected function configureFile(InputInterface $input, OutputInterface $output, $name, $path)
    {
        $yaml = Yaml::parse(file_get_contents($this->currentDir.DIRECTORY_SEPARATOR.$path));

        $helper = $this->getHelper('question');

        $output->writeln("<comment>Please configure $name parameters</comment>");

        $response = array();
        foreach ($yaml as $key => $value) {
            $question = $this->createQuestion($key, $yaml[$key]);
            $response[$key] = $helper->ask($input, $output, $question);

            if (is_int($yaml[$key])) {
                $response[$key] = (int) $response[$key];
            }
        }

        $yamlNew = Yaml::dump($response);
        file_put_contents($this->currentDir.DIRECTORY_SEPARATOR.$path, $yamlNew);

        $output->writeln("\n<info>New $name values:</info>");
        $output->writeln(sprintf('<info>%s</info>', $yamlNew));

        return $this;
    }

}