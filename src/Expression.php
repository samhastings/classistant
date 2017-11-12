<?php

declare(strict_types=1);

namespace SamHastings\Classistant;

class Expression
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public static function create($value)
    {
        return new self($value);
    }

    public function getValue()
    {
        return $this->value;
    }
}
