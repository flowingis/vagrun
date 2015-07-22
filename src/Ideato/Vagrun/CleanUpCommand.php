<?php

namespace Ideato\Vagrun;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @codeCoverageIgnore
 */
class CleanUpCommand extends Command
{
    protected $currentDir;

    /** @var Filesystem */
    protected $fs;

    protected function configure()
    {
        $this
            ->setName('cleanup')
            ->setDescription('Remove vagrant template from current directory')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Set path of current working directory')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Remove vagrant template without interaction');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->fs = new Filesystem();
        $this->currentDir = getcwd().DIRECTORY_SEPARATOR;

        if ($input->getOption('path')) {
            $this->currentDir = $input->getOption('path').DIRECTORY_SEPARATOR;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $force = $input->getOption('force');

        $filesToRemove = array(
            $this->currentDir.'Vagrantfile',
            $this->currentDir.'.vagrant',
            $this->currentDir.'vagrant',
        );

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            "\nAre you really sure you want to delete all vagrant files? The action cannot be reverted. (Y/n)\n",
            false
        );

        if (!$force && !$helper->ask($input, $output, $question)) {
            $output->writeln('<comment>Clean up action skipped</comment>');

            return;
        }

        try {
            $this->fs->remove($filesToRemove);

            $output->writeln("<info>Vagrant template successfully removed</info>\n");
        } catch (\Exception $e) {
            $output->writeln(sprintf(
                "<error>An error occurred while trying to remove vagrant template files</error>\n%s",
                $e->getMessage()
            ));
        }
    }
}
