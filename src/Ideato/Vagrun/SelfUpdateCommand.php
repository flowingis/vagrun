<?php

namespace Ideato\Vagrun;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommand extends Command
{
    const BASE_URL = 'http://ideatosrl.github.io/vagrun/';

    /** @var OutputInterface */
    protected $output;

    protected function configure()
    {
        $this
            ->setName('selfupdate')
            ->setDescription('Updates Vagrun to the latest version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this
            ->checkIsLatestVersion()
            ->getLatestVersion($input, $output)
        ;
    }

    protected function checkIsLatestVersion()
    {
        return $this;
    }

    protected function getLatestVersion(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Updating Vagrun to the latest version...</info>");

        $remoteFilename = self::BASE_URL.'vagrun.phar';
        $localFilename = $_SERVER['argv'][0];
        $tempFilename = basename($localFilename, '.phar').'-temp.phar';
        file_put_contents($tempFilename, file_get_contents($remoteFilename));
        try {
            chmod($tempFilename, 0777 & ~umask());
            // test the phar validity
            $phar = new \Phar($tempFilename);
            // free the variable to unlock the file
            unset($phar);
            rename($tempFilename, $localFilename);
        } catch (\Exception $e) {
            @unlink($tempFilename);
            if (!$e instanceof \UnexpectedValueException && !$e instanceof \PharException) {
                throw $e;
            }
            $output->writeln('<error>The download is corrupted ('.$e->getMessage().').</error>');
            $output->writeln('<error>Please re-run the selfupdate command to try again.</error>');
        }
    }
}