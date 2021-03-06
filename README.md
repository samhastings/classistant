# Classistant

Classistant provides a fluent interface for generating PHP classes in PHP.

## Why?

I needed a way of creating PHP classes on the fly, defining property and method names based on user input. Creating PHP code based on user input might sound like an awful idea, but it actually worked pretty well.

## Requirements

Classistant requires PHP 7.0 or higher, both to run the code generator and to run the PHP it outputs. I’m afraid I have no plans to write a PHP 5.x version of this library.

## Limitations

Currently, Classistant does not support any of the following features. I do however plan to implement these in the future.

- Anonymous class generation.
- Declaring a method as `abstract` without also declaring the class as such will throw a fatal error in PHP. No such warning is thrown when trying to generate such code with Classistant.
- Interface generation.

## Installation

Install via Composer:

```
composer require samhastings/classistant
```

## Configuration

There’s not much this library offers in the way of configuration, apart from allowing you to customise the automatic indentation to match your personal preference.

By default, generated code is indented with four spaces. Override this as you wish with the following `Config` class properties.

```php
// Four spaces (default behaviour)
\SamHastings\Classistant\Config::$indentChar = ' ';
\SamHastings\Classistant\Config::$indentWidth = 4;

// Tab
\SamHastings\Classistant\Config::$indentChar = "\t";
\SamHastings\Classistant\Config::$indentWidth = 1;
```

## Usage

A complete example of functionality is shown below. I’ve commented it throughout but the code will be self-explanatory.

All classes reside within the `SamHastings\Classistant` namespace.

```php
use SamHastings\Classistant\{
    ClassGenerator,
    ConstantGenerator,
    Expression,
    FileGenerator,
    MethodGenerator,
    ParameterGenerator,
    PropertyGenerator,
    Visibility
};

$class = ClassGenerator::create('MyClass')
    // Create the class inside a namespace if you wish.
    ->setNamespace('This\\Is\\My\\Namespace')

    // You can extend a single class... (see note below for explanation of why the
    // leading backslash is required.)
    ->extends('\\'.ExtendMe::class)

    // ...implement multiple interfaces...
    ->implements('\\'.ImplementMe::class)
    ->implements('\\'.ImplementMeToo::class)

    // ...and use multiple traits.
    ->use('\\'.UseMe::class)
    ->use('\\'.UseMeToo::class)

    // Make it abstract..
    ->abstract()

    // ...or final.
    ->final()

    // Constants are defined in the ConstantGenerator class.
    ->addConstant(ConstantGenerator::create('CONSTANT_NAME', 'value'))

    // You can change the visibility of a constant. (PHP 7.1+ only.)
    ->addConstant(ConstantGenerator::create('PRIVATE_CONSTANT', 42, Visibility::PRIVATE))

    // Let’s add a couple of properties. The default visibility is public. When
    // you add a property, getter and setter methods are automatically generated.
    ->addProperty(PropertyGenerator::create('myPublicProperty', Visibility::PUBLIC))
    ->addProperty(PropertyGenerator::create('myPrivateProperty', Visibility::PRIVATE))
    ->addProperty(PropertyGenerator::create('myProtectedProperty', Visibility::PROTECTED))

    // You can also specify the property’s default value. When the property’s type
    // is set to `bool`, the accessor method name changes to `is*`. In all other
    // cases, the name will be `get*`.
    ->addProperty(
        PropertyGenerator::create('active', Visibility::PRIVATE, 'bool')
            ->setDefaultValue(true)
    )

    // Static properties are supported.
    ->addProperty(
        PropertyGenerator::create('myStaticProperty')
            ->static()
            ->setDefaultValue('bar')
    )

    // Use the Expression class to set a PHP expression as a property’s default
    // value. This can also be used when defining default parameter values.
    ->addProperty(
        PropertyGenerator::create('foo', Visibility::PRIVATE)
            ->setDefaultValue(Expression::create('self::CONSTANT_NAME'))
    )

    // You can also define the data type of the property. This affects the method
    // signatures of the generated getter and setter methods.
    ->addProperty(PropertyGenerator::create('date', Visibility::PRIVATE, '\\'.\DateTime::class))

    // Of course, you may want to disable getter and setter generation. The second
    // and third arguments passed to addProperty() disable getters and setters,
    // respectively.
    ->addProperty(PropertyGenerator::create('date'), false, false)

    // Methods are public by default, but visibility can be changed as with
    // properties.
    ->addMethod(
        MethodGenerator::create('doSomething', Visibility::PRIVATE)
            // Add a plain old parameter.
            ->addParameter(ParameterGenerator::create('name'))

            // You can type-hint a parameter.
            ->addParameter(ParameterGenerator::create('date', '\\'.\DateTime::class))

            // You can also specify a default value for the parameter, making it
            // optional.
            ->addParameter(
                ParameterGenerator::create('location', 'string')
                    ->setDefaultValue(null)
            )
            ->addParameter(
                ParameterGenerator::create('favoriteColor', 'string')
                    ->setDefaultValue('red')
            )

            // And you can mark the parameter type as nullable. (PHP 7.1+ only.)
            ->addParameter(
                ParameterGenerator::create('age', 'int')
                    ->nullable()
            )

            // Return types are supported. Pass `true` as the second argument to
            // make the return value nullable. (PHP 7.1+ only.)
            ->setReturnType('string', true)

            // The method body is just a string.
            ->setBody("return 'something';")
    )

    // If a method simply needs to return a PHP value, you can specify the value
    // and the script will write the appropriate method body for you. Note, this
    // will take precedence over any past or future calls to `setBody()`.
    ->addMethod(
        MethodGenerator::create('getValidLocales')
            ->return([
                'en_GB',
                'en_US',
                'fr_FR',
                'fr_CA',
            ])
    )

    // Variadic functions are supported.
    ->addMethod(
        MethodGenerator::create('addThings')
            ->addParameter(
                ParameterGenerator::create('things', 'string')
                    ->variadic()
            )
            ->setBody('$this->things = $things;')
    )

    // A method can also be static or final.
    ->addMethod(
        MethodGenerator::create('doSomethingElse')
            ->static()
            ->final()
            ->setBody('return true;')
    )

    // And finally, it can be abstract, in which case any body you specify will
    // be ignored.
    ->addMethod(
        MethodGenerator::create('implementMe')
            ->abstract()
    )
;

// To write the output to a file, use the FileGenerator class.
FileGenerator::create()
    ->setStrictTypes(true)
    ->setBody($class)
    ->writeTo(__DIR__.'/classes/MyClass.php')
;

// Alternatively, you can capture the output of FileGenerator and do whatever
// you want with it.
$php = FileGenerator::create()
    ->setStrictTypes(true)
    ->setBody($class)
    ->getPhp()
;
```

### A note on class resolution

The `::class` constant, available on all classes, interfaces and traits as of PHP 5.5, provides a handy shortcut for fetching a fully-qualified class name. This will *never* contain a leading backslash, as a class name in a string is always treated by PHP as being relative to the global namespace when called with the `new` operator.

Whenever you’re using Classistant to generate namespaced classes, *always* prepend a backslash to arguments passed to `ClassGenerator::extends()`, `ClassGenerator::implements()` and `ClassGenerator::use()`. The same rules apply if you’re passing a class or interface name as a method parameter or return type. Otherwise, your generated code will generate a fatal error as PHP looks for said class names in the same namespace as your generated class.
