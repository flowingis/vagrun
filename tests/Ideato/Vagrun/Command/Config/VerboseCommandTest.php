<?php

namespace Ideato\Vagrun\Test\Command\Config;

use Ideato\Vagrun\Test\CommandTestCase;
use Ideato\Vagrun\Command\Config\VerboseCommand;
use Symfony\Component\Yaml\Yaml;

class VerboseCommandTest extends CommandTestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testShouldRaiseExceptionWhenVagrantfileIsNotFound()
    {
        $command = new VerboseCommand();
        $commandTester = $this->executeCommand(
            $command,
            [],
            '/'
        );
    }

    /**
     * @covers Ideato\Vagrun\Command\Config\VerboseCommand
     */
    public function testItCanUpdateConfigurationFile()
    {
        $command = new VerboseCommand();
        $commandTester = $this->executeCommand(
            $command,
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
        $this->assertContains('config.vm.box = "hashicorp/precise64"', $vagrantFile);
    }
}
