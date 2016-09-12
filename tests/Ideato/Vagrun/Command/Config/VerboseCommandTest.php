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
                '/var/www/vagrun',
                'vagrun.dev',
                '{{ host }}',
                '{{ synced_folder }}',
            ]
        );

        $output = $commandTester->getDisplay();
        $this->assertContains('ram: 1024', $output);
        $this->assertContains('cpus: 1', $output);
        $this->assertContains('ipaddress: 10.10.10.111', $output);
        $this->assertContains('name: test-box', $output);
        $this->assertContains('synced_folder: /var/www/vagrun', $output);

        $updatedConfigFile = Yaml::parse(file_get_contents($this->currentDir.'vagrant/vagrantconfig.yml'));
        $expected = array(
            'ram' => 1024,
            'cpus' => 1,
            'ipaddress' => '10.10.10.111',
            'name' => 'test-box',
            'synced_folder' => '/var/www/vagrun',
            'hosts' => ['vagrun.dev']
        );
        $this->assertEquals($expected, $updatedConfigFile);
    }
}
