<?php
declare(strict_types=1);

namespace Producer;

use AutoShell\Console;
use Caplet\Caplet;
use Producer\Infra\Api;
use Producer\Infra\Api\ApiFactory;
use Producer\Infra\Fsio\HomeFsio;
use Producer\Infra\Fsio\RepoFsio;
use Producer\Infra\Repo\RepoFactory;
use Producer\Infra\Repo;
use Producer\Infra\Stdlog;
use Psr\Log\LoggerInterface;

class ProducerContainer extends Caplet
{
    public function __construct(
        protected string $homedir,
        protected string $repodir,
        protected mixed $stdout = STDOUT,
        protected mixed $stderr = STDERR
    ) {
        parent::__construct([
            HomeFsio::class => [
                'root' => $homedir,
            ],
            RepoFsio::class => [
                'root' => $repodir,
            ],
            Stdlog::class => [
                'stdout' => $stdout,
                'stderr' => $stderr,
            ],
        ]);

        $this->factory(
            Api::class,
            fn (Caplet $caplet) : Api => $caplet->get(ApiFactory::class)->new()
        );

        $this->factory(
            Console::class,
            fn (Caplet $caplet) : Console => Console::new(
                namespace: 'Producer\Sapi\Cli\Command',
                directory: __DIR__ . '/Sapi/Cli/Command/',
                factory: [$caplet, 'get'],
                header: "Producer 2.0.0 by Paul M. Jones and contributors." . PHP_EOL . PHP_EOL,
            )
        );

        $this->factory(
            LoggerInterface::class,
            fn (Caplet $caplet) : Stdlog => $caplet->get(Stdlog::class)
        );

        $this->factory(
            Repo::class,
            fn (Caplet $caplet) : Repo => $caplet->get(RepoFactory::class)->new()
        );
    }
}
