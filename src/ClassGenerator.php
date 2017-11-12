<?php

declare(strict_types=1);

namespace SamHastings\Classistant;

class ClassGenerator implements GeneratorInterface
{
    private $name;
    private $namespace;
    private $parent;
    private $interfaces = [];
    private $traits = [];
    private $constants = [];
    private $properties = [];
    private $methods = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function create(string $name)
    {
        return new self($name);
    }

    public function setNamespace(string $namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function extends(string $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function implements(string $interface)
    {
        $this->interfaces[] = $interface;

        return $this;
    }

    public function use(string $trait)
    {
        $this->traits[] = $trait;

        return $this;
    }

    public function addConstant(ConstantGenerator $constant)
    {
        $this->constants[] = $constant;

        return $this;
    }

    public function addProperty(PropertyGenerator $property, bool $getter = true, bool $setter = true)
    {
        $this->properties[] = $property;

        if ($getter) {
            $this->methods[] = MethodGenerator::createGetter($property);
        }

        if ($setter) {
            $this->methods[] = MethodGenerator::createSetter($property);
        }

        return $this;
    }

    public function addMethod(MethodGenerator $method)
    {
        $this->methods[] = $method;

        return $this;
    }

    public function getPhp(): string
    {
        $php = '';

        if (null !== $this->namespace) {
            $php .= sprintf('namespace %s;', $this->namespace);
            $php .= PHP_EOL.PHP_EOL;
        }

        $php .= sprintf('class %s ', $this->name);

        if (null !== $this->parent) {
            $php .= sprintf('extends %s ', $this->parent);
        }

        if ($this->interfaces) {
            $php .= sprintf('implements %s ', implode(', ', $this->interfaces));
        }

        $php .= PHP_EOL.'{'.PHP_EOL;

        if ($this->traits) {
            $php .= Util::indent(sprintf('use %s; ', implode(', ', $this->traits)));
            $php .= PHP_EOL.PHP_EOL;
        }

        $php .= Util::indent(Util::group($this->constants));
        $php .= PHP_EOL.PHP_EOL;

        $php .= Util::indent(Util::group($this->properties));
        $php .= PHP_EOL.PHP_EOL;

        $php .= Util::indent(Util::group($this->methods, PHP_EOL.PHP_EOL));
        $php .= PHP_EOL.PHP_EOL;

        $php = rtrim($php);
        $php .= PHP_EOL.'}'.PHP_EOL;

        return $php;
    }

    public function __toString()
    {
        return $this->getPhp();
    }
}
