<?php

namespace Ideato\Vagrun\Test\Command;

use Ideato\Vagrun\Command\ConfigCommand;
use Ideato\Vagrun\Command\Config;
use Ideato\Vagrun\Test\CommandTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigCommandCommandTest extends CommandTestCase
{
    /**
     * @covers Ideato\Vagrun\Command\ConfigCommand
     */
    public function testShouldRunBaseConfigCommand()
    {
        $commandTester = $this->executeCommand(
            false,
            ['testvagrun']
        );

        $output = $commandTester->getDisplay();
        $this->assertContains('Vagrant successfully configured!', $output);
    }

    /**
     * @covers Ideato\Vagrun\Command\ConfigCommand
     */
    public function testShouldRunVerboseConfigCommand()
    {
        $commandTester = $this->executeCommand(
            true,
            [
                '1024',
                '1',
                '10.10.10.111',
                'test-box',
                'hashicorp/precise64',
                '/var/www/vagrun',
            ]
        );

        $output = $commandTester->getDisplay();
        $this->assertContains('ram: 1024', $output);
        $this->assertContains('cpus: 1', $output);
        $this->assertContains('ipaddress: 10.10.10.111', $output);
        $this->assertContains('name: test-box', $output);
        $this->assertContains('Base box: hashicorp/precise64', $output);
        $this->assertContains('Synced folder: /var/www/vagrun', $output);
    }


    /**
     * @param array $userAnswers
     * @param bool|string $path
     *
     * @return CommandTester
     */
    protected function executeCommand($verbose = false, $userAnswers = [], $path = false)
    {
        $configCommand = new ConfigCommand();

        $application = new Application();
        $application->add(new ConfigCommand());
        $application->add(new Config\BaseCommand());
        $application->add(new Config\VerboseCommand());

        $command = $application->find($configCommand->getName());
        $commandTester = new CommandTester($command);

        $this->setUserAnswersForCommandQuestion(
            $userAnswers,
            $command
        );

        if(false === $path) {
            $path = $this->currentDir;
        }

        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--path' => $path,
                '--verbose' => $verbose
            )
        );

        return $commandTester;
    }

}
