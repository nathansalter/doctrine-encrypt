<?php

namespace AvidCiTests\Doctrine\Command;

use AvidCi\Doctrine\Command\CreateEncryptionKeyCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class CreateEncryptionKeyCommandTest extends TestCase
{
    private CreateEncryptionKeyCommand $command;
    private CommandTester $tester;

    protected function setUp(): void
    {
        $this->command = new CreateEncryptionKeyCommand();
        $this->tester = new CommandTester($this->command);
    }

    public function testGeneratesEncryptionKey()
    {
        $rc = $this->tester->execute([]);
        $this->assertEquals(0, $rc);
        $this->assertStringContainsString('Encryption key', $this->tester->getDisplay());
    }
}
