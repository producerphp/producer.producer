<?php
declare(strict_types=1);

namespace Producer\Sapi\Cli\Command;

use AutoShell\Help;
use Producer\App\LogService;

#[Help("Show repository log entries since last release.")]
class Log
{
    public function __construct(protected LogService $logService)
    {
    }

    public function __invoke() : int
    {
        $this->logService->__invoke();
        return 0;
    }
}
