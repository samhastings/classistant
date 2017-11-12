<?php

declare(strict_types=1);

namespace SamHastings\Classistant;

use SamHastings\Classistant\Exception\InvalidIdentifierException;

class ConstantGenerator implements GeneratorInterface
{
    private $name;
    private $value;

    public function __construct(string $name, $value)
    {
        if (!Util::isValidIdentifier($name)) {
            throw new InvalidIdentifierException(sprintf(
                '%s is not a valid PHP identifier',
                $name
            ));
        }

        $this->name = $name;
        $this->value = $value;
    }

    public static function create(string $name, $value)
    {
        return new self($name, $value);
    }

    public function getPhp(): string
    {
        return sprintf('const %s = %s;', $this->name, Util::export($this->value));
    }

    public function __toString()
    {
        return $this->getPhp();
    }
}
