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

    protected function configureFile(InputInterface $input, OutputInterface $output, $name, $path)
    {
        $yaml = Yaml::parse(file_get_contents($this->currentDir.DIRECTORY_SEPARATOR.$path));

        $helper = $this->getHelper('question');

        $output->writeln("<comment>Please configure $name parameters</comment>");

        $response = array();
        foreach ($yaml as $key => $value) {
            $default = $yaml[$key];
            if (is_array($default)) {
                $default = array_shift($default);
            }

            $question = $this->createQuestion($key, $default);
            $response[$key] = $helper->ask($input, $output, $question);

            if (is_int($yaml[$key])) {
                $response[$key] = (int) $response[$key];
            }

            if (is_array($yaml[$key])) {
                $response[$key] = [ $response[$key] ];
            }
        }

        $yamlNew = Yaml::dump($response);
        file_put_contents($this->currentDir.DIRECTORY_SEPARATOR.$path, $yamlNew);

        $output->writeln("\n<info>New $name values:</info>");
        $output->writeln(sprintf('<info>%s</info>', $yamlNew));

        return $this;
    }
}
