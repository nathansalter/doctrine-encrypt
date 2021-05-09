<?php

namespace AvidCi\Doctrine\Factory;

use AvidCi\Doctrine\LifecycleListener\EncryptDecryptListener;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\HiddenString\HiddenString;

class EncryptDecryptListenerFactory
{
    public function __construct(
        private string $encryptionKey,
    ){
    }

    public function create(): EncryptDecryptListener
    {
        if ('notset' === $this->encryptionKey) {
            throw new \RuntimeException('Unable to create listener, AVIDCI_ENCRYPTION_KEY env var not set');
        }
        return new EncryptDecryptListener(KeyFactory::importEncryptionKey(new HiddenString($this->encryptionKey)));
    }
}
