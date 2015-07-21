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
        $already = ' already ';

        $output->writeln("\n<info>Our pixies are verifing your Vagrun version...</info>");

        if (!$this->checkIsLatestVersion()) {
            $this->getLatestVersion($input, $output);
            $already = ' ';
        }

        $output->writeln("\n<info>...congrats! Your Vagrun is".$already.'up to date!</info>');
    }

    protected function getLatestVersion(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("\n<info>Yup, your Vagrun needs to be updated! Our sprites are working on that!</info>");

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
            $output->writeln("\n<error>What a pity: the download is corrupted (".$e->getMessage().').</error>');
            $output->writeln('<error>Please master, give us another chance: re-run the selfupdate command to try again.</error>');
        }
    }

    protected function checkIsLatestVersion()
    {
        $localVersion = $this->getApplication()->getVersion();
        $latestVersion = trim(file_get_contents(self::BASE_URL.'version', false));

        if ($localVersion !== $latestVersion) {
            return false;
        }

        return true;
    }
}
