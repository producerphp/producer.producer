<?php
declare(strict_types=1);

namespace Producer;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Stringable;

class Stdlog extends AbstractLogger
{
    /**
     *
     * Write to stderr for these log levels.
     *
     * @var string[]
     *
     */
    protected array $stderrLevels = [
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
    ];

    /**
     * @param resource $stdout
     * @param resource $stderr
     */
    public function __construct(
        protected mixed $stdout,
        protected mixed $stderr
    ) {
    }

    /**
     * @param mixed[] $context
     */
    public function log(
        mixed $level,
        string|Stringable $message,
        array $context = []
    ) : void
    {
        $replace = [];

        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        $message = strtr((string) $message, $replace) . PHP_EOL;

        $handle = $this->stdout;

        if (in_array($level, $this->stderrLevels)) {
            $handle = $this->stderr;
        }

        fwrite($handle, $message);
    }
}
