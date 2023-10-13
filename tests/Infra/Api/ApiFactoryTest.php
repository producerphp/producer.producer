<?php
declare(strict_types=1);

namespace Producer\Infra\Api;

use Producer\Infra\Repo\Git;
use Producer\Infra\Config;

class ApiFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider githubProvider
     */
    public function testThatItReturnsAppropriateApiImplementationForGithub(
        string $host,
        string $origin,
        string $repoName,
    ) : void
    {
        $repo = $this->mockRepo($origin);
        $config = $this->mockConfig([
            'github_hostname' => $host,
            'github_username' => 'producer',
            'github_token' => 'token',
        ]);
        $apiFactory = new ApiFactory($repo, $config);
        $api = $apiFactory->new();
        $this->assertInstanceOf(Github::class, $api);
        $this->assertEquals($repoName, $api->getRepoName());
    }

    /**
     * @return array<string[]>
     */
    public static function githubProvider()
    {
        return [
            [
                'github.enterprise.com',
                'git@github.enterprise.com:producer/producer.git',
                'producer/producer',
            ],
            [
                'api.github.com',
                'git@github.com:producer/producer.git',
                'producer/producer',
            ],
        ];
    }

    /**
     * @param mixed[] $data
     */
    protected function mockConfig(array $data) : Config
    {
        $config = $this->createMock(Config::class);
        $valueMap = [];

        foreach ($data as $arg => $return) {
            $valueMap[] = [$arg, $return];
        }

        $config
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($valueMap));
        return $config;
    }

    protected function mockRepo(string $origin) : Git
    {
        $repo = $this->createMock(Git::class);
        $repo
            ->expects($this->any())
            ->method('getOrigin')
            ->will($this->returnValue($origin));
        return $repo;
    }
}
