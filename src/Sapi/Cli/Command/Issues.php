<?php
declare(strict_types=1);

namespace Producer\Sapi\Cli\Command;

use AutoShell\Help;
use Producer\App\IssuesService;

#[Help("Show open issues from the remote origin.")]
class Issues
{
    public function __construct(protected IssuesService $issuesService)
    {
    }

    public function __invoke() : int
    {
        $this->issuesService->__invoke();
        return 0;
    }
}
