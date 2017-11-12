<?php

declare(strict_types=1);

namespace SamHastings\Classistant;

class FileGenerator implements GeneratorInterface
{
    private $strictTypes = false;
    private $body;

    public static function create()
    {
        return new self();
    }

    public function setStrictTypes(bool $strictTypes)
    {
        $this->strictTypes = $strictTypes;

        return $this;
    }

    public function setBody(GeneratorInterface $body)
    {
        $this->body = $body;

        return $this;
    }

    public function getPhp(): string
    {
        $php = '<?php';
        $php .= PHP_EOL.PHP_EOL;

        if ($this->strictTypes) {
            $php .= 'declare(strict_types=1);';
            $php .= PHP_EOL.PHP_EOL;
        }

        $php .= $this->body;

        return $php;
    }

    public function __toString()
    {
        return $this->getPhp();
    }

    public function writeTo(string $filename, bool $overwrite = true)
    {
        if (!$overwrite && file_exists($filename)) {
            throw new \RuntimeException(sprintf(
                'Unable to write to %s, file already exists.',
                $filename
            ));
        }

        $fp = fopen($filename, 'w');
        $success = fwrite($fp, $this->getPhp());

        if (false === $success) {
            throw new \RuntimeException('Error writing file.');
        }

        return $this;
    }
}
