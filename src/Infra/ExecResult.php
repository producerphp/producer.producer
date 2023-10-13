<?php
declare(strict_types=1);

namespace Producer\Infra;

use Stringable;

class ExecResult
{
    /**
     * @param string[] $lines
     */
    public function __construct(
        public array $lines,
        public string $lastLine,
        public int $exitCode,
    ) {
    }

    public function isError() : bool
    {
        return $this->exitCode !== 0;
    }

    public function asException() : Exception
    {
        return new Exception($this->lastLine, $this->exitCode);
    }

    public function asLongException() : Exception
    {
        return new Exception(implode(PHP_EOL, $this->lines), $this->exitCode);
    }
}
