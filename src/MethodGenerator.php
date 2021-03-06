<?php

declare(strict_types=1);

namespace SamHastings\Classistant;

use SamHastings\Classistant\Exception\InvalidIdentifierException;

class MethodGenerator implements GeneratorInterface
{
    private $name;
    private $abstract = false;
    private $final = false;
    private $visibility;
    private $static = false;
    private $parameters = [];
    private $returnType;
    private $nullable;
    private $body;
    private $returnValue;
    private $hasReturnValue = false;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $visibility
     */
    public function __construct(string $name, string $visibility = Visibility::PUBLIC)
    {
        if (!Util::isValidIdentifier($name)) {
            throw new InvalidIdentifierException(sprintf(
                '%s is not a valid PHP identifier',
                $name
            ));
        }

        $this->name = $name;
        $this->visibility = $visibility;
    }

    /**
     * Static creator method for easier fluent code.
     *
     * @param string $name
     * @param string $visibility
     *
     * @return $this
     */
    public static function create(string $name, string $visibility = Visibility::PUBLIC)
    {
        return new self($name, $visibility);
    }

    /**
     * Declares an abstract method
     *
     * @return $this
     */
    public function abstract()
    {
        $this->abstract = true;

        return $this;
    }

    /**
     * Declares a final method
     *
     * @return $this
     */
    public function final()
    {
        $this->final = true;

        return $this;
    }

    /**
     * Create a getter method for the specified property.
     *
     * @param PropertyGenerator $property
     * @param string $visibility the getter’s visibility; default `public`
     *
     * @return self
     */
    public static function createGetter(PropertyGenerator $property, string $visibility = Visibility::PUBLIC)
    {
        $type = $property->getType();
        $prefix = $type === 'bool' ? 'is' : 'get';

        $getter = self::create($prefix.ucfirst($property->getName()), $visibility)
            ->setBody(sprintf('return $this->%s;', $property->getName()))
        ;

        if (null !== $type) {
            $getter->setReturnType($type);
        }

        return $getter;
    }

    /**
     * Create a setter method for the specified property.
     *
     * @param PropertyGenerator $property
     * @param string $visibility the setter’s visibility; default `public`
     *
     * @return $this
     */
    public static function createSetter(PropertyGenerator $property, string $visibility = Visibility::PUBLIC)
    {
        return self::create('set'.ucfirst($property->getName()), $visibility)
            ->addParameter(ParameterGenerator::create($property->getName(), $property->getType()))
            ->setBody(sprintf('$this->%1$s = $%1$s;', $property->getName()))
        ;
    }

    /**
     * Sets the return value. If this is set, this will override the method body.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function return($value)
    {
        $this->returnValue = $value;
        $this->hasReturnValue = true;

        return $this;
    }

    /**
     * Sets the method’s visibility.
     *
     * @param string $visibility
     *
     * @return $this
     */
    public function setVisibility(string $visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Makes the method static.
     *
     * @return $this
     */
    public function static()
    {
        $this->static = true;

        return $this;
    }

    /**
     * Sets the method’s return type.
     *
     * @param string $returnType
     * @param bool   $nullable
     *
     * @return $this
     */
    public function setReturnType(string $returnType, bool $nullable = false)
    {
        $this->returnType = $returnType;
        $this->nullable = $nullable;

        return $this;
    }

    /**
     * Adds a method parameter.
     *
     * @param ParameterGenerator $parameter
     *
     * @return $this
     */
    public function addParameter(ParameterGenerator $parameter)
    {
        $this->parameters[] = $parameter;

        return $this;
    }

    /**
     * Sets the method’s body.
     *
     * @param string $body
     *
     * @return $this
     */
    public function setBody(string $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Returns the full method declaration in PHP.
     *
     * @return string
     */
    public function getPhp(): string
    {
        $php = sprintf(
            '%s%s%s%s function %s(%s)%s',
            $this->final ? 'final ' : '',
            $this->abstract ? 'abstract ' : '',
            $this->visibility,
            $this->static ? ' static' : '',
            $this->name,
            Util::group($this->parameters, ', '),
            null === $this->returnType ? '' : sprintf(': %s%s', $this->nullable ? '?' : '', $this->returnType)
        );

        if ($this->abstract) {
            $php .= ';'.PHP_EOL.PHP_EOL;
        } else {
            $php .= PHP_EOL.'{'.PHP_EOL;

            if ($this->hasReturnValue) {
                $php .= Util::indent('return '.Util::export($this->returnValue).';');
            } else {
                $php .= Util::indent($this->body);
            }

            $php .= PHP_EOL.'}';
        }

        return $php;
    }

    public function __toString()
    {
        return $this->getPhp();
    }
}
