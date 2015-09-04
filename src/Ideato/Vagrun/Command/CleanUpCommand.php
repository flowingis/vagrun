<?php

namespace Ideato\Vagrun\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;

class CleanUpCommand extends Command
{
    protected $currentDir;

    private $fs;

    public function __construct(Filesystem $fs = null) {
        parent::__construct(null);
        if ($fs == null) {
            $fs = new Filesystem();
        }
        $this->fs = $fs;
    }

    protected function configure()
    {
        $this
            ->setName('cleanup')
            ->setDescription('Remove vagrant template from current directory')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Set path of current working directory')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Remove vagrant template without interaction');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->currentDir = getcwd().DIRECTORY_SEPARATOR;

        if ($input->getOption('path')) {
            $this->currentDir = $input->getOption('path').DIRECTORY_SEPARATOR;
        }

        $forceOption = $input->getOption('force');
        if (!$forceOption) {
            if (  $this->askConfirmation($input, $output) == false ) {
                $output->writeln('<comment>Clean up action skipped</comment>');
                return;
            }
        }

        try {
            $this->removeVagrantFiles();
            $output->writeln("<info>Vagrant template successfully removed</info>\n");
        } catch (\Exception $e) {
            $output->writeln("<error>An error occurred while trying to remove vagrant template files</error>\n");
            $output->writeln($e->getMessage());
        }
    }

    private function removeVagrantFiles()
    {
        $filesToRemove = array(
            $this->currentDir . 'Vagrantfile',
            $this->currentDir . '.vagrant',
            $this->currentDir . 'vagrant',
        );

        $this->fs->remove($filesToRemove);
    }

    private function askConfirmation(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            "\nAre you really sure you want to delete all vagrant files? The action cannot be reverted. (Y/n)\n",
            false
        );
        $confirmation = $helper->ask($input, $output, $question);
        return $confirmation;
    }
}
