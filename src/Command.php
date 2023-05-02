<?php
declare(strict_types=1);

namespace Producer;

use Producer\Api\ApiInterface;
use Producer\Repo\RepoInterface;
use Psr\Log\LoggerInterface;

abstract class Command
{
    public function __construct(
        protected LoggerInterface $logger,
        protected RepoInterface $repo,
        protected ApiInterface $api,
        protected Config $config
    ) {
    }
}
