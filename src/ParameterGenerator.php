<?php

declare(strict_types=1);

namespace SamHastings\Classistant;

class ParameterGenerator implements GeneratorInterface
{
    private $name;
    private $type;
    private $defaultValue;
    private $hasDefaultValue = false;
    private $variadic = false;

    public function __construct(string $name, string $type = null)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public static function create(string $name, string $type = null)
    {
        return new self($name, $type);
    }

    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        $this->hasDefaultValue = true;

        return $this;
    }

    public function variadic()
    {
        $this->variadic = true;

        return $this;
    }

    public function getPhp(): string
    {
        $php = sprintf(
            '%s %s$%s',
            $this->type,
            $this->variadic ? '...' : '',
            $this->name
        );

        if ($this->hasDefaultValue) {
            $php .= sprintf(' = %s', Util::export($this->defaultValue));
        }

        return trim($php);
    }

    public function __toString()
    {
        return $this->getPhp();
    }
}
