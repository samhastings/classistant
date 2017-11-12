<?php

declare(strict_types=1);

namespace SamHastings\Classistant;

interface GeneratorInterface
{
    public function getPhp(): string;
    public function __toString();
}
