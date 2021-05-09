<?php

namespace AvidCi\Doctrine\Attribute;

use \Attribute;
use Doctrine\ORM\Mapping\Annotation;
use ParagonIE\Halite\Halite;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Encrypt implements Annotation
{
    // Default. No mapping
    public const STRING = 'string';
    // Contents are run through json_encode/json_decode
    public const JSON = 'array';
    // Contents are (int) cast on retrieval
    public const INT = 'int';
    // Contents are (float) case on retrieval
    public const FLOAT = 'float';

    public const TYPES = [
        self::STRING,
        self::JSON,
        self::INT,
        self::FLOAT,
    ];

    /**
     * @param string $type Set this to one of the type constants
     */
    public function __construct(
        public string $type = 'string',
        public string $encoding = Halite::ENCODE_BASE64URLSAFE,
    ) {
    }
}
