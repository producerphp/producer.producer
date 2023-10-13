<?php
declare(strict_types=1);

namespace Producer\Infra;

use Psr\Log\LoggerInterface;

class Exec
{
    public function __construct(protected LoggerInterface $logger)
    {
    }

    public function logged(string $command) : ExecResult
    {
        $this->logger->debug("> {$command}");
        $execResult = $this->result($command);

        foreach ($execResult->lines as $line) {
            $this->logger->debug("< {$line}");
        }

        return $execResult;
    }

    public function result(string $command) : ExecResult
    {
        $lastLine = exec($command, $lines, $exitCode);

        if ($lastLine === false) {
            throw new Exception("Command failed: {$command}");
        }

        return new ExecResult($lines, $lastLine, $exitCode);
    }
}
