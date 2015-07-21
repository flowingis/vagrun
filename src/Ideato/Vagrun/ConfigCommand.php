<?php

namespace Ideato\Vagrun;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class ConfigCommand extends Command
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

    protected function configure()
    {
        $this
            ->setName('config')
            ->setDescription('Configure your vagrant machine')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Set path of current working directory');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->fs = new Filesystem();

        $this->currentDir = getcwd().DIRECTORY_SEPARATOR;

        if ($input->getOption('path')) {
            $this->currentDir = $input->getOption('path').DIRECTORY_SEPARATOR;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this
            ->checkVagrunIsInstalled()
            ->configureFiles($input, $output)
            ->configureVagrantfile($input, $output)
        ;
    }

    protected function checkVagrunIsInstalled()
    {
        $vagrantFile = rtrim($this->currentDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'Vagrantfile';

        if (!$this->fs->exists($vagrantFile)) {
            throw new \RuntimeException('Vagrant template is not initialized. Please run `vagrun init` instead.');
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

    protected function createQuestion($question, $default)
    {
        $question = sprintf('<question>%s</question> (<comment>%s</comment>): ', $question, $default);

        return new Question($question, $default);
    }
}
