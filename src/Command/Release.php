<?php
declare(strict_types=1);

namespace Producer\Command;

/**
 *
 * Release the package after validating it.
 *
 * @package producer/producer
 *
 */
class Release extends Validate
{
    /**
     *
     * The command logic.
     *
     * @param array $argv Command line arguments.
     *
     * @return mixed
     *
     */
    public function __invoke(array $argv)
    {
        $this->logger->warning("THIS WILL RELEASE THE PACKAGE.");
        parent::__invoke($argv);
        $this->logger->info("Releasing $this->package $this->version");
        $this->api->release($this->repo, $this->version);
        $this->logger->info("Released $this->package $this->version !");
    }
}
