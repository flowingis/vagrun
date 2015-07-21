<?php

namespace Ideato\Vagrun\Test;

use Ideato\Vagrun\ConfigCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;

class ConfigCommandTest extends \PHPUnit_Framework_TestCase
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
EOD;
        file_put_contents($this->currentDir.'vagrant/vagrantconfig.yml', $config);
    }

    public function testItCanUpdateConfigurationFile()
    {
        $command = new ConfigCommand();
        $commandTester = $this->executeCommand(
            $command,
            [
                '1024',
                '1',
                '10.10.10.111',
                'test-box',
                '/var/www/vagrun',
            ]
        );

        $output = $commandTester->getDisplay();
        $this->assertContains('ram: 1024', $output);
        $this->assertContains('cpus: 1', $output);
        $this->assertContains('ipaddress: 10.10.10.111', $output);
        $this->assertContains('name: test-box', $output);
        $this->assertContains('Synced folder: /var/www/vagrun', $output);

        $updatedConfigFile = Yaml::parse(file_get_contents($this->currentDir.'vagrant/vagrantconfig.yml'));
        $expected = array(
            'ram' => 1024,
            'cpus' => 1,
            'ipaddress' => '10.10.10.111',
            'name' => 'test-box',
        );
        $this->assertEquals($expected, $updatedConfigFile);

        $vagrantFile = file_get_contents($this->currentDir.'Vagrantfile');
        $this->assertEquals(2, substr_count($vagrantFile, 'vagrant/vagrantconfig.yml'));
        $this->assertEquals(3, substr_count($vagrantFile, '/var/www/vagrun'));
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }

    protected function tearDown()
    {
        shell_exec('rm -rf '.$this->currentDir);
    }

    /**
     * @param $command
     * @param $answers
     */
    private function setUserAnswersForCommandQuestion($answers, $command)
    {
        $helper = $command->getHelper('question');
        $helper->setInputStream(
            $this->getInputStream(
                implode("\n", $answers)
            )
        );
    }

    /**
     * @param $command
     * @param $userAnswers
     *
     * @return CommandTester
     */
    private function executeCommand($command, $userAnswers)
    {
        $application = new Application();
        $application->add($command);

        $command = $application->find('config');
        $commandTester = new CommandTester($command);

        $this->setUserAnswersForCommandQuestion(
            $userAnswers,
            $command
        );

        $commandTester->execute(
            array(
                'command' => 'config',
                '--path' => $this->currentDir,
            )
        );

        return $commandTester;
    }
}
