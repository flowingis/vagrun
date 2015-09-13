<?php


namespace Ideato\Vagrun\Command;


use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Helper\ProgressBar;
use GuzzleHttp\Message\Response;


class DownloadProgressBar
{
    private $progressBar = null;

    public function __construct(ConsoleOutput $output) {
        $this->output = $output;
    }

    public function update($size, $downloaded, $client, $request, Response $response) {
        if ($response->getStatusCode() >= 300) {
            return;
        }
        if (null === $this->progressBar) {
            $this->progressBar = $this->createProgressBar($size);
        }
        $this->progressBar->setProgress($downloaded);
    }

    public function finish() {
        if ($this->progressBar !== null) {
            $this->progressBar->finish();
        }

        $this->output->writeln("\n");
    }

    /**
     * Utility method to show the number of bytes in a readable format.
     *
     * @param int $bytes The number of bytes to format
     *
     * @return string The human readable string of bytes (e.g. 4.32MB)
     */
    private function formatSize($bytes)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = $bytes ? floor(log($bytes, 1024)) : 0;
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return number_format($bytes, 2).' '.$units[$pow];
    }

    private function createProgressBar($size)
    {
        ProgressBar::setPlaceholderFormatterDefinition('max', function (ProgressBar $bar) {
            return $this->formatSize($bar->getMaxSteps());
        });
        ProgressBar::setPlaceholderFormatterDefinition('current', function (ProgressBar $bar) {
            return str_pad($this->formatSize($bar->getProgress()), 11, ' ', STR_PAD_LEFT);
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
        return $progressBar;
    }
}