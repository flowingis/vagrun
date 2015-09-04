<?php

namespace Ideato\Vagrun\Test\Command;

use Ideato\Vagrun\Command\CleanUpCommand;
use Ideato\Vagrun\Test\CommandTestCase;
use Symfony\Component\Filesystem\Filesystem;


class CleanUpCommandTest extends CommandTestCase
{

    public function testShouldNotRemoveFilesWhenThereIsNoConfirmation()
    {
        $fakeFs = new FakeFilesystem();
        $cleanUpCommand = new CleanUpCommand($fakeFs);
        $this->executeCommand($cleanUpCommand, ['n'], false);

        $this->assertEmpty($fakeFs->fileRemoved);
    }

    public function testShouldRemoveFilesWhenThereIsConfirmation()
    {
        $fakeFs = new FakeFilesystem();
        $cleanUpCommand = new CleanUpCommand($fakeFs);
        $this->executeCommand($cleanUpCommand, ['Y'], false);

        $this->assertCount(3, $fakeFs->fileRemoved);
    }

    public function testShouldRemoveFilesWithoutConfirmationWhenForced()
    {
        $fakeFs = new FakeFilesystem();
        $cleanUpCommand = new CleanUpCommand($fakeFs);
        $this->executeCommand($cleanUpCommand, ['Y'], false);

        $this->assertCount(3, $fakeFs->fileRemoved);
    }

}

class FakeFilesystem extends Filesystem {
    public $fileRemoved = [];

    public function remove($files) {
        $this->fileRemoved = $files;
    }
}
