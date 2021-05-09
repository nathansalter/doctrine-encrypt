<?php

namespace AvidCi\Doctrine\Command;

use ParagonIE\Halite\KeyFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateEncryptionKeyCommand extends Command
{
    protected function configure()
    {
        $this->setName('avidci:encryption:create-key');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $key = KeyFactory::generateEncryptionKey();
            $output->writeln('Encryption key (Store in .env under AVIDCI_ENCRYPTION_KEY):');
            $output->writeln(KeyFactory::export($key)->getString());
        } catch (\Throwable $error) {
            $output->writeln('Failed to generate Encryption key');
            $output->writeln($error->getMessage());
            return 1;
        }

        return 0;
    }
}
