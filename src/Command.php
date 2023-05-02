<?php
declare(strict_types=1);

namespace Producer;

use Producer\Api;
use Producer\Repo;
use Psr\Log\LoggerInterface;

abstract class Command
{
    public function __construct(
        protected LoggerInterface $logger,
        protected Repo $repo,
        protected Api $api,
        protected Config $config
    ) {
    }
}
