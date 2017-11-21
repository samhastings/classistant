<?php

declare(strict_types=1);

namespace SamHastings\Classistant;

use SamHastings\Classistant\Exception\InvalidIdentifierException;

class PropertyGenerator implements GeneratorInterface
{
    private $name;
    private $visibility;
    private $type;
    private $defaultValue;
    private $static = false;

    public function __construct(string $name, string $visibility = Visibility::PUBLIC, string $type = null)
    {
        if (!Util::isValidIdentifier($name)) {
            throw new InvalidIdentifierException(sprintf(
                '%s is not a valid PHP identifier',
                $name
            ));
        }

        $this->name = $name;
        $this->visibility = $visibility;
        $this->type = $type;
    }

    public static function create(string $name, string $visibility = Visibility::PUBLIC, string $type = null)
    {
        return new self($name, $visibility, $type);
    }

    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function static()
    {
        $this->static = true;

        return $this;
    }

    public function getPhp(): string
    {
        $php = sprintf(
            '%s%s $%s',
            $this->visibility,
            $this->static ? ' static' : '',
            $this->name
        );

        if (null !== $this->defaultValue) {
            $php .= sprintf(" = %s", Util::export($this->defaultValue));
        }

        $php .= ';';

        return $php;
    }

    public function __toString()
    {
        return $this->getPhp();
    }
}
