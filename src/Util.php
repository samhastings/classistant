<?php

declare(strict_types=1);

namespace SamHastings\Classistant;

class Util
{
    public static $indentChar = ' ';
    public static $indentWidth = 4;

    /**
     * This class must not be instantiated
     */
    private function __construct()
    {
    }

    /**
     * Checks that the supplied identifier is suitable for use as a PHP class,
     * variable, method, etc name.
     *
     * @param string $identifier
     *
     * @return bool
     */
    public static function isValidIdentifier(string $identifier): bool
    {
        // http://www.php.net/manual/en/language.variables.basics.php
        $pattern = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

        return 1 === preg_match($pattern, $identifier);
    }

    /**
     * Wrapper for PHP’s var_export function that also allows PHP literals to
     * be passed as Expression instances.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public static function export($value)
    {
        if ($value instanceof Expression) {
            return $value->getValue();
        }

        return var_export($value, true);
    }

    /**
     * Indents lines of text by the specified depth
     *
     * @param string $code
     * @param int $depth [description]
     *
     * @return string
     */
    public static function indent(string $code, int $depth = 1): string
    {
        $indented = preg_replace('/^/m', str_repeat(self::$indentChar, self::$indentWidth * $depth), $code);
        $indented = preg_replace('/^\s+$/m', '', $indented);

        return $indented;
    }

    /**
     * Renders a group of generated code blocks
     *
     * @param GeneratorInterface[] $group
     * @param string $separator
     *
     * @return string
     */
    public static function group(array $group, $separator = PHP_EOL): string
    {
        return implode($separator, array_map(function (GeneratorInterface $generator) {
            return $generator->getPhp();
        }, $group));
    }
}
