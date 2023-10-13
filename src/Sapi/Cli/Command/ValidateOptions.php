<?php
declare(strict_types=1);

namespace Producer\Sapi\Cli\Command;

use AutoShell\Option;
use AutoShell\Options;

class ValidateOptions implements Options
{
    public function __construct(
        #[Option('r,release', help: "Release the package after validation.")]
        public readonly ?bool $release,
    ) {
    }
}
