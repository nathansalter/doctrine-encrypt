<?php

namespace LifecycleListener;

use AvidCi\Doctrine\Attribute\Encrypt;
use AvidCi\Doctrine\LifecycleListener\EncryptDecryptListener;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\HiddenString\HiddenString;
use PHPUnit\Framework\TestCase;

class EncryptDecryptListenerTest extends TestCase
{
    private EncryptDecryptListener $listener;

    protected function setUp(): void
    {
        $this->listener = new EncryptDecryptListener(KeyFactory::importEncryptionKey(new HiddenString('31400400a0734783b6131d62dbafa7df672874485221f8c81055afb1ca22a224c791fc3d8e7482279986ff51e988c2096346ada7557cf82b143643f625e197687d0cd0d58e3a27c9eaaa79b76c594f970774ee7d30923dd6b1be00f638b87267df04feba')));
    }

    public function testSkipsWhenNoPropertiesRequireEncryption()
    {
        $entity = new class {
            public $foo = 'foo';
            public $bar = 'bar';
        };
        $this->listener->preFlush($this->createEvent($entity));
        $this->listener->postLoad($this->createEvent($entity));

        $this->assertEquals('foo', $entity->foo);
        $this->assertEquals('bar', $entity->bar);
    }

    public function testEncryptsSingleProperty()
    {
        $entity = new class {
            public $foo = 'foo';
            #[Encrypt]
            public $bar = 'bar';
        };

        $this->listener->preFlush($this->createEvent($entity));
        $this->assertNotEquals('bar', $entity->bar);
        $this->listener->postLoad($this->createEvent($entity));
        $this->assertEquals('bar', $entity->bar);
    }

    public function testEncryptsMultipleProperties()
    {
        $entity = new class {
            #[Encrypt(type: Encrypt::STRING)]
            public string $string = 'string';
            #[Encrypt(type: Encrypt::JSON)]
            public string|array $json = ['foo' => 'rawr'];
            #[Encrypt(type: Encrypt::INT)]
            public string|int $int = 15009283;
            #[Encrypt(type: Encrypt::FLOAT)]
            public string|float $float = 3.145126153248;
        };

        $this->listener->preFlush($this->createEvent($entity));
        $this->assertNotEquals('string', $entity->string);
        $this->assertNotEquals(['foo' => 'rawr'], $entity->json);
        $this->assertNotEquals(15009283, $entity->int);
        $this->assertNotEquals(3.145126153248, $entity->float);

        $this->listener->postLoad($this->createEvent($entity));
        $this->assertEquals('string', $entity->string);
        $this->assertEquals(['foo' => 'rawr'], $entity->json);
        $this->assertEquals(15009283, $entity->int);
        $this->assertEquals(3.145126153248, $entity->float);
    }

    private function createEvent($entity): LifecycleEventArgs
    {
        return new LifecycleEventArgs($entity, $this->createMock(ObjectManager::class));
    }
}
