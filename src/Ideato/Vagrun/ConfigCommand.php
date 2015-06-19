<?php

namespace Ideato\Vagrun;

use Distill\Distill;
use Distill\Exception\IO\Input\FileCorruptedException;
use Distill\Exception\IO\Input\FileEmptyException;
use Distill\Exception\IO\Output\TargetDirectoryNotWritableException;
use Distill\Strategy\MinimumSize;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Subscriber\Progress\Progress;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class ConfigCommand extends Command
{
    protected $currentDir;

    /** @var Filesystem */
    protected $fs;
    /** @var OutputInterface */
    protected $output;

    protected function configure()
    {
        $this
            ->setName('config')
            ->setDescription('Configure your vagrant machine');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->fs = new Filesystem();

        $this->currentDir = getcwd() . DIRECTORY_SEPARATOR;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this
            ->checkVagrunIsInstalled()
            ->setVagrantconfig($input, $output)
        ;
    }

    protected function checkVagrunIsInstalled()
    {
        $vagrantFile = rtrim($this->currentDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Vagrantfile';

        if (!$this->fs->exists($vagrantFile)) {
            throw new \RuntimeException('Vagrant template is not initialized. Please run `vagrun init` instead.');
        }

        return $this;
    }

    protected function setVagrantconfig(InputInterface $input, OutputInterface $output)
    {
        $yaml = Yaml::parse(file_get_contents($this->currentDir . DIRECTORY_SEPARATOR . '/vagrant/vagrantconfig.yml'));

        $helper = $this->getHelper('question');

        $question = new Question('Set ram: <fg=yellow>[' . $yaml["ram"] . ']</fg=yellow> ', $yaml["ram"]);
        $response["ram"] = (int)$helper->ask($input, $output, $question);
        $this->output->writeln("<fg=magenta>Ram: " . $response["ram"] . "MB</fg=magenta>\n");

        $question = new Question('Set cpus: <fg=yellow>[' . $yaml["cpus"] . ']</fg=yellow> ', $yaml["cpus"]);
        $response["cpus"] = (int)$helper->ask($input, $output, $question);
        $this->output->writeln("<fg=magenta>Cpus: " . $response["cpus"] . "</fg=magenta>\n");

        $question = new Question('Set IP address: <fg=yellow>[' . $yaml["ipaddress"] . ']</fg=yellow> ', $yaml["ipaddress"]);
        $response["ipaddress"] = (string)$helper->ask($input, $output, $question);
        $this->output->writeln("<fg=magenta>IP address: " . $response["ipaddress"] . "</fg=magenta>\n");

        $question = new Question('Set VM name: <fg=yellow>[' . $yaml["name"] . ']</fg=yellow> ', $yaml["name"]);
        $response["name"] = (string)$helper->ask($input, $output, $question);
        $this->output->writeln("<fg=magenta>VM name: " . $response["name"] . "</fg=magenta>\n");

        $yamlNew = Yaml::dump($response);

        file_put_contents($this->currentDir . DIRECTORY_SEPARATOR . '/vagrant/vagrantconfig.yml', $yamlNew);

        return $this;
    }
}