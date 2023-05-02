<?php
declare(strict_types=1);

namespace Producer\Command;

use Producer\Api\ApiInterface;
use Producer\Config;
use Producer\Repo\RepoInterface;
use Psr\Log\LoggerInterface;

/**
 *
 * Base command class.
 *
 * @package producer/producer
 *
 */
abstract class AbstractCommand implements CommandInterface
{
    /**
     *
     * The logger.
     *
     * @var LoggerInterface
     *
     */
    protected $logger;

    /**
     *
     * The local repository.
     *
     * @var RepoInterface
     *
     */
    protected $repo;

    /**
     *
     * The remote API.
     *
     * @var ApiInterface
     *
     */
    protected $api;

    /**
     *
     * The producer configuration
     * 
     * @var Config
     *
     */
    protected $config;

    /**
     *
     * Constructor.
     *
     * @param LoggerInterface $logger The logger.
     *
     * @param RepoInterface $repo The local repository.
     *
     * @param ApiInterface $api The remote API.
     * 
     * @param Config $config The global and project configuration.
     * 
     */
    public function __construct(
        LoggerInterface $logger,
        RepoInterface $repo,
        ApiInterface $api,
        Config $config
    ) {
        $this->logger = $logger;
        $this->repo = $repo;
        $this->api = $api;
        $this->config = $config;
    }

    /**
     *
     * The command logic.
     *
     * @param array $argv Command line arguments.
     *
     * @return mixed
     *
     */
    abstract public function __invoke(array $argv);
}
