<?php
declare(strict_types=1);

namespace Producer\Api;

use Producer\Repo\Git;
use Producer\Config;

class ApiFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider githubProvider
     */
    public function testThatItReturnsAppropriateApiImplementationForGithub($host, $origin, $repoName)
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

    public static function githubProvider()
    {
        return [
            ['github.enterprise.com', 'git@github.enterprise.com:producer/producer.git', 'producer/producer'],
            ['api.github.com', 'git@github.com:producer/producer.git', 'producer/producer'],
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockConfig($data)
    {
        $config = $this->createMock(Config::class);

        foreach ($data as $arg => $return) {
            $valueMap[] = [$arg, $return];
        }

        $config->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($valueMap));

        return $config;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockRepo($origin)
    {
        $repo = $this->createMock(Git::class);

        $repo->expects($this->any())
            ->method('getOrigin')
            ->will($this->returnValue($origin));

        return $repo;
    }
}
