<?php
declare(strict_types=1);

namespace Producer;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 *
 * A basic logger implementation that writes to standard output and error.
 *
 * @package producer/producer
 *
 */
class Stdlog extends AbstractLogger
{
    /**
     *
     * The stdout file handle.
     *
     * @var resource
     *
     */
    protected $stdout;

    /**
     *
     * The stderr file handle.
     *
     * @var resource
     *
     */
    protected $stderr;

    /**
     *
     * Write to stderr for these log levels.
     *
     * @var array
     *
     */
    protected $stderrLevels = [
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
    ];

    /**
     *
     * Constructor.
     *
     * @param resource $stdout Write to stdout on this handle.
     *
     * @param resource $stderr Write to stderr on this handle.
     *
     */
    public function __construct($stdout, $stderr)
    {
        $this->stdout = $stdout;
        $this->stderr = $stderr;
    }

    /**
     *
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function log($level, $message, array $context = [])
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        $message = strtr($message, $replace) . PHP_EOL;

        $handle = $this->stdout;
        if (in_array($level, $this->stderrLevels)) {
            $handle = $this->stderr;
        }

        fwrite($handle, $message);
    }
}
