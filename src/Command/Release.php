<?php
declare(strict_types=1);

namespace Producer\Command;

use AutoShell\Help;
use AutoShell\Options;
use Producer\Command;

#[Help("Release the repository at <version>.")]
class Release extends Validate
{
    public function __invoke(
        Options $options,

        #[Help("The version to release.")]
        string $version

    ) : int
    {
        $this->logger->warning("THIS WILL RELEASE THE PACKAGE.");
        parent::__invoke($version);
        $this->logger->info("Releasing $this->package $this->version");
        $this->api->release($this->repo, $this->version);
        $this->logger->info("Released $this->package $this->version !");
        return 0;
    }
}
