<?php

namespace Ideato\Vagrun\Test;

use Ideato\Vagrun\ConfigCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class ConfigCommandTest extends \PHPUnit_Framework_TestCase
{
    protected $currentDir;

    protected function setUp()
    {
        $this->currentDir = sys_get_temp_dir() . '/';
        shell_exec('rm -rf ' . $this->currentDir . '*');
        touch($this->currentDir . 'Vagrantfile');
        shell_exec('cd ' . $this->currentDir . '&& mkdir vagrant && touch vagrant/vagrantconfig.yml');

        $config = <<<EOD
ram: 2048
cpus: 2
ipaddress: 10.10.10.10
name: vagrant-box-name
EOD;
        file_put_contents($this->currentDir . 'vagrant/vagrantconfig.yml', $config);
    }

    public function testExecute()
    {


        $application = new Application();
        $application->add(new ConfigCommand());

        $command = $application->find('config');

        $commandTester = new CommandTester($command);

        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream(
            "1024\n".
            "2\n".
            "10.10.10.111\n".
            "test-box\n"
        ));

        $commandTester->execute(array(
            'command' => $command->getName(),
            '--path' => $this->currentDir
        ));

        $output = $commandTester->getDisplay();
        $this->assertContains('Ram: 1024', $output);
        $this->assertContains('Cpus: 2', $output);
        $this->assertContains('IP address: 10.10.10.111', $output);
        $this->assertContains('VM name: test-box', $output);

        $yaml = Yaml::parse(file_get_contents($this->currentDir . '/vagrant/vagrantconfig.yml'));
        $expected = array(
            "ram" => 1024,
            "cpus" => 2,
            "ipaddress" => '10.10.10.111',
            "name" => 'test-box'
        );

        $this->assertEquals($expected, $yaml);
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}
