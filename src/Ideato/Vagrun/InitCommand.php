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
use Symfony\Component\Filesystem\Filesystem;

class InitCommand extends Command
{
    protected $remoteFileUrl = 'https://github.com/ideatosrl/vagrant-php-template/archive/master.zip';
    protected $downloadedFilePath;
    protected $currentDir;

    /** @var Filesystem */
    protected $fs;
    /** @var OutputInterface */
    protected $output;

    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Creates a new vagrant template.');
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
            ->checkVagrunIsInstallable()
            ->download()
            ->extract()
            ->cleanUp();
    }

    protected function checkVagrunIsInstallable()
    {
        $vagrantFile = rtrim($this->currentDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Vagrantfile';

        if ($this->fs->exists($vagrantFile)) {
            throw new \RuntimeException('Vagrant template is already initialized');
        }

        return $this;
    }

    protected function download()
    {
        $this->output->writeln("\n Downloading Vagrant PHP Template...\n");

        $distill = new Distill();
        $archiveFile = $distill
            ->getChooser()
            ->addFile($this->remoteFileUrl)
            ->getPreferredFile();

        /** @var ProgressBar|null $progressBar */
        $progressBar = null;
        $downloadCallback = function ($size, $downloaded, $client, $request, Response $response) use (&$progressBar) {
            // Don't initialize the progress bar for redirects as the size is much smaller
            if ($response->getStatusCode() >= 300) {
                return;
            }
            if (null === $progressBar) {
                ProgressBar::setPlaceholderFormatterDefinition('max', function (ProgressBar $bar) {
                    return $this->formatSize($bar->getMaxSteps());
                });
                ProgressBar::setPlaceholderFormatterDefinition('current', function (ProgressBar $bar) {
                    return str_pad($this->formatSize($bar->getStep()), 11, ' ', STR_PAD_LEFT);
                });
                $progressBar = new ProgressBar($this->output, $size);
                $progressBar->setFormat('%current%/%max% %bar%  %percent:3s%%');
                $progressBar->setRedrawFrequency(max(1, floor($size / 1000)));
                $progressBar->setBarWidth(60);
                if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
                    $progressBar->setEmptyBarCharacter('░'); // light shade character \u2591
                    $progressBar->setProgressCharacter('');
                    $progressBar->setBarCharacter('▓'); // dark shade character \u2593
                }
                $progressBar->start();
            }
            $progressBar->setProgress($downloaded);
        };

        $client = $this->getGuzzleClient();
        $client->getEmitter()->attach(new Progress(null, $downloadCallback));
        // store the file in a temporary hidden directory with a random name
        $this->downloadedFilePath = rtrim($this->currentDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.' . uniqid(time()) . DIRECTORY_SEPARATOR . 'vagrun.' . pathinfo($archiveFile, PATHINFO_EXTENSION);

        try {
            $response = $client->get($archiveFile);
        } catch (ClientException $e) {

            throw new \RuntimeException(sprintf(
                "There was an error downloading from github.com server:\n%s",
                $e->getMessage()
            ), null, $e);
        }

        $this->fs->dumpFile($this->downloadedFilePath, $response->getBody());
        if (null !== $progressBar) {
            $progressBar->finish();
            $this->output->writeln("\n");
        }

        return $this;
    }

    protected function extract()
    {
        $this->output->writeln(" Preparing project...\n");

        try {
            $distill = new Distill();
            $extractionSucceeded = $distill->extract($this->downloadedFilePath, $this->currentDir);
        } catch (FileCorruptedException $e) {
            throw new \RuntimeException(sprintf(
                "Vagrun can't be installed because the downloaded package is corrupted.\n" .
                "To solve this issue, try executing this command again:\n%s",
                $this->getExecutedCommand()
            ));
        } catch (FileEmptyException $e) {
            throw new \RuntimeException(sprintf(
                "Vagrun can't be installed because the downloaded package is empty.\n" .
                "To solve this issue, try executing this command again:\n%s",
                $this->getExecutedCommand()
            ));
        } catch (TargetDirectoryNotWritableException $e) {
            throw new \RuntimeException(sprintf(
                "Vagrun can't be installed because the installer doesn't have enough\n" .
                "permissions to uncompress and rename the package contents.\n" .
                "To solve this issue, check the permissions of the %s directory and\n" .
                "try executing this command again:\n%s",
                $this->currentDir, $this->getExecutedCommand()
            ));
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf(
                "Vagrun can't be installed because the downloaded package is corrupted\n" .
                "or because the installer doesn't have enough permissions to uncompress and\n" .
                "rename the package contents.\n" .
                "To solve this issue, check the permissions of the %s directory and\n" .
                "try executing this command again:\n%s",
                $this->currentDir, $this->getExecutedCommand()
            ), null, $e);
        }
        if (!$extractionSucceeded) {
            throw new \RuntimeException(sprintf(
                "Vagrun can't be installed because the downloaded package is corrupted\n" .
                "or because the uncompress commands of your operating system didn't work."
            ));
        }
        
        return $this;
    }


    /**
     * Returns the Guzzle client configured according to the system environment
     * (e.g. it takes into account whether it should use a proxy server or not).
     *
     * @return Client
     */
    protected function getGuzzleClient()
    {
        $options = array();
        // check if the client must use a proxy server
        if (!empty($_SERVER['HTTP_PROXY']) || !empty($_SERVER['http_proxy'])) {
            $proxy = !empty($_SERVER['http_proxy']) ? $_SERVER['http_proxy'] : $_SERVER['HTTP_PROXY'];
            $options['proxy'] = $proxy;
        }

        return new Client($options);
    }


    /**
     * Utility method to show the number of bytes in a readable format.
     *
     * @param int $bytes The number of bytes to format
     *
     * @return string The human readable string of bytes (e.g. 4.32MB)
     */
    protected function formatSize($bytes)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = $bytes ? floor(log($bytes, 1024)) : 0;
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return number_format($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Returns the executed command with all its arguments
     * (e.g. "symfony new blog 2.3.6").
     *
     * @return string
     */
    protected function getExecutedCommand()
    {
        $commandBinary = $_SERVER['PHP_SELF'];
        $commandBinaryDir = dirname($commandBinary);
        $pathDirs = explode(PATH_SEPARATOR, $_SERVER['PATH']);
        if (in_array($commandBinaryDir, $pathDirs)) {
            $commandBinary = basename($commandBinary);
        }
        $commandName = $this->getName();

        return sprintf('%s %s', $commandBinary, $commandName);
    }

    /**
     * Removes all the temporary files and directories created to
     * download the project and removes files that don't make
     * sense in a proprietary project.
     *
     * @return $this
     */
    protected function cleanUp()
    {
        $this->output->writeln(" Cleaning up the project...\n");

        $this->fs->remove(dirname($this->downloadedFilePath));

        try {
            $this->fs->rename($this->currentDir . 'vagrant-php-template-master', $this->currentDir . 'vagrant');
            $this->fs->copy($this->currentDir . 'vagrant/Vagrantfile', $this->currentDir . 'Vagrantfile');

            $filesToRemove = array(
                $this->currentDir . 'vagrant/.gitignore',
                $this->currentDir . 'vagrant/README.md',
                $this->currentDir . 'vagrant/Vagrantfile',
            );
            $this->fs->remove($filesToRemove);

            $fileToRename = $this->currentDir . DIRECTORY_SEPARATOR . 'vagrant' . DIRECTORY_SEPARATOR;
            $this->fs->rename($fileToRename . 'vagrantconfig.dist.yml', $fileToRename . 'vagrantconfig.yml');

            $this->output->writeln(" <info>Project successfully initialized</info>\n");
        } catch (\Exception $e) {
            // don't throw an exception in case any of the files cannot
            // be removed, because this is just an enhancement, not something mandatory
            // for the project
        }

        return $this;
    }
}