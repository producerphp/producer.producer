<?php

namespace Producer;

use Producer\Api\Github;
use ReflectionClass;

class ProducerContainerTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @dataProvider githubProvider
     */
    public function testThatItReturnsAppropriateApiImplementationForGithub($host, $origin, $repoName)
    {
        $container = new ProducerContainer($_SERVER['HOME'], getcwd(), STDOUT, STDERR);

        // Unlock ProducerContainer#newApi...
        $reflector = new ReflectionClass($container);
        $newApi = $reflector->getMethod('newApi');
        $newApi->setAccessible(true);

        $config = $this->mockConfig([
            'github_hostname' => $host,
            'github_username' => 'producer',
            'github_token' => 'token',
        ]);

        // $container->newApi($origin, $config) : ApiInterfaces
        $api = $newApi->invokeArgs($container, [$origin, $config]);

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
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($data as $arg => $return) {
            $valueMap[] = [$arg, $return];
        }

        $config->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($valueMap));

        return $config;
    }

}
