<?php

declare(strict_types=1);

namespace SamHastings\Classistant;

use SamHastings\Classistant\Exception\InvalidIdentifierException;

class ConstantGenerator implements GeneratorInterface
{
    private $name;
    private $value;
    private $visibility;

    public function __construct(string $name, $value, string $visibility = null)
    {
        if (!Util::isValidIdentifier($name)) {
            throw new InvalidIdentifierException(sprintf(
                '%s is not a valid PHP identifier',
                $name
            ));
        }

        $this->name = $name;
        $this->value = $value;
        $this->visibility = $visibility;
    }

    public static function create(string $name, $value, string $visibility = null)
    {
        return new self($name, $value, $visibility);
    }

    public function getPhp(): string
    {
        return sprintf(
            '%sconst %s = %s;',
            $this->visibility ? $this->visibility.' ' : '',
            $this->name,
            Util::export($this->value)
        );
    }

    public function __toString()
    {
        return $this->getPhp();
    }
}
