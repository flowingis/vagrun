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

        foreach($yaml as $key => $value)
        {
            $question = new Question('Set ' . $key . ': <fg=yellow>[' . $value . ']</fg=yellow> ', $value);

            //var_dump($yaml);
            //NON FUNGE, restituisce tutti 1

            var_dump(gettype($value));

            $type = gettype($value);
            $response = settype($helper->ask($input, $output, $question), $type);

            $this->output->writeln("\n<fg=magenta>You choosed " . $response . "</fg=magenta>\n");
        }

        return $this;
    }
}