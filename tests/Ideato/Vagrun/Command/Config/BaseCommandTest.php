<?php

namespace Ideato\Vagrun\Test\Command\Config;

use Ideato\Vagrun\Command\Config\BaseCommand;
use Ideato\Vagrun\Test\CommandTestCase;
use Symfony\Component\Yaml\Yaml;

class BaseCommandTest extends CommandTestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Vagrant template is not initialized. Please run `vagrun init` instead.
     */
    public function testShouldRaiseExceptionWhenVagrantfileIsNotFound()
    {
        $command = new BaseCommand();
        $commandTester = $this->executeCommand(
            $command,
            [],
            '/tmp/'
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Aborted
     */
    public function testShouldRaiseExceptionWhenProjectNameIsNotProvided()
    {
        $command = new BaseCommand();
        $commandTester = $this->executeCommand(
            $command,
            []
        );
    }

    /**
     * @covers Ideato\Vagrun\Command\Config\BaseCommand
     */
    public function testItCanUpdateConfigurationFile()
    {
        $command = new BaseCommand();
        $commandTester = $this->executeCommand(
            $command,
            ['testvagrun']
        );

        $output = $commandTester->getDisplay();
        $this->assertContains('Vagrant successfully configured!', $output);
        $this->assertContains('Now run `vagrant up` to create your Vagrant machine', $output);

        $updatedConfigFile = Yaml::parse(file_get_contents($this->currentDir.'vagrant/vagrantconfig.yml'));
        $expected = array(
            'ram' => 2048,
            'cpus' => 2,
            'ipaddress' => '10.10.10.10',
            'name' => 'testvagrun',
        );
        $this->assertEquals($expected, $updatedConfigFile);

        $vagrantFile = file_get_contents($this->currentDir.'Vagrantfile');
        $this->assertEquals(2, substr_count($vagrantFile, 'vagrant/vagrantconfig.yml'));
        $this->assertEquals(3, substr_count($vagrantFile, '/var/www/testvagrun'));
        $this->assertContains('config.vm.box = "ubuntu/trusty64"', $vagrantFile);
    }
}
