<?php

namespace Ideato\Vagrun\Test;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

abstract class CommandTestCase extends \PHPUnit_Framework_TestCase
{
    protected $currentDir;

    protected function setUp()
    {
        $this->currentDir = sys_get_temp_dir().'/vagrun.'.uniqid(time()).'/';
        shell_exec('mkdir '.$this->currentDir);
        shell_exec(sprintf('cp %s %s/Vagrantfile', __DIR__.'/../../fixtures/Vagrantfile.template', $this->currentDir));
        shell_exec('cd '.$this->currentDir.'&& mkdir vagrant && touch vagrant/vagrantconfig.yml');

        $config = <<<EOD
ram: 2048
cpus: 2
ipaddress: 10.10.10.10
name: vagrant-box-name
synced_folder: /var/www
hosts:
  - vagrant-box-name.dev
EOD;
        file_put_contents($this->currentDir.'vagrant/vagrantconfig.yml', $config);
    }

    /**
     * @param $command
     * @param array       $userAnswers
     * @param bool|string $path
     *
     * @return CommandTester
     */
    protected function executeCommand($command, $userAnswers = [], $path = false)
    {
        $application = new Application();
        $application->add($command);

        $command = $application->find($command->getName());
        $commandTester = new CommandTester($command);

        $this->setUserAnswersForCommandQuestion(
            $userAnswers,
            $command
        );

        if (false === $path) {
            $path = $this->currentDir;
        }

        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--path' => $path,
            )
        );

        return $commandTester;
    }

    /**
     * @param \Symfony\Component\Console\Command\Command $command
     * @param $answers
     */
    protected function setUserAnswersForCommandQuestion($answers, $command)
    {
        $helper = $command->getHelper('question');
        $helper->setInputStream(
            $this->getInputStream(
                implode("\n", $answers)
            )
        );
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }

    public function tearDown()
    {
        shell_exec("rm -rf {$this->currentDir}");
    }
}
