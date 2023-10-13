<?php
declare(strict_types=1);

namespace Producer\Sapi\Cli\Command;

use AutoShell\Help;
use Producer\App\IssuesService;
use Producer\App\ValidateService;

#[Help("Validate, and optionally release, the repository as _version_.")]
class Validate
{
    public function __construct(
        protected ValidateService $validateService,
        protected IssuesService $issuesService,
    ) {
    }

    public function __invoke(
        ValidateOptions $options,

        #[Help("Validate at this version.")]
        string $version,
    ) : int
    {
        $this->validateService->__invoke($version, (bool) ($options->release ?? false));
        $this->issuesService->__invoke();
        return 0;
    }
}
