<?php

namespace AvidCi\Doctrine\LifecycleListener;

use AvidCi\Doctrine\Attribute\Encrypt;
use Doctrine\ORM\Event\LifecycleEventArgs;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;
use Symfony\Component\PropertyAccess\PropertyAccess;

class EncryptDecryptListener
{
    public function __construct(
        private EncryptionKey $symmetricKey,
    ) {
    }

    public function preFlush(LifecycleEventArgs $event)
    {
        $this->applyEncryptedFields($event->getEntity(), function (Encrypt $encryptAttribute, $entity, \ReflectionProperty $property) {
            $accessor = PropertyAccess::createPropertyAccessor();
            $unencrypted = $this->mapToDbValue($encryptAttribute, $accessor->getValue($entity, $property->getName()));
            $encrypted = Crypto::encrypt(new HiddenString($unencrypted), $this->symmetricKey, $encryptAttribute->encoding);
            $accessor->setValue($entity, $property->getName(), $encrypted);
        });
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        $this->applyEncryptedFields($event->getEntity(), function (Encrypt $encryptAttribute, $entity, \ReflectionProperty $property) {
            $accessor = PropertyAccess::createPropertyAccessor();
            $encrypted = $accessor->getValue($entity, $property->getName());
            $unencrypted = Crypto::decrypt($encrypted, $this->symmetricKey, $encryptAttribute->encoding)->getString();
            $accessor->setValue($entity, $property->getName(), $this->mapFromDbValue($encryptAttribute, $unencrypted));
        });
    }

    private function applyEncryptedFields($entity, callable $apply)
    {
        $reflectedEntity = new \ReflectionClass($entity);
        foreach ($reflectedEntity->getProperties() as $property) {
            $encryptAttribute = $property->getAttributes(Encrypt::class)[0] ?? false;
            if (!$encryptAttribute) {
                continue;
            }
            $encryptAttribute = $encryptAttribute->newInstance();
            $apply($encryptAttribute, $entity, $property);
        }
    }

    private function mapToDbValue(Encrypt $encrypt, $value): string
    {
        return match ($encrypt->type) {
            Encrypt::STRING => $value,
            Encrypt::JSON => json_encode($value),
            Encrypt::FLOAT, Encrypt::INT => (string) $value,
        };
    }

    private function mapFromDbValue(Encrypt $encrypt, string $value): string|array|int|float
    {
        return match ($encrypt->type) {
            Encrypt::STRING => $value,
            Encrypt::JSON => json_decode($value, true, flags: JSON_THROW_ON_ERROR),
            Encrypt::FLOAT => (float) $value,
            Encrypt::INT => (int) $value,
        };
    }
}
