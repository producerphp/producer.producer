<?php
declare(strict_types=1);

namespace Producer;

use Producer\Api\Github;

class GitHubTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider remoteProvider
     */
    public function testRepoNameCanBeDerivedFromRemote(string $remote, string $hostname, string $repoName) : void
    {
        $api = new Github(
            $remote,
            $hostname,
            'username',
            'token'
        );

        $this->assertEquals($repoName, $api->getRepoName());
    }

    /**
     * @return array<string[]>
     */
    public static function remoteProvider() : array
    {   
        return [
            ['git@github.com:user/repo.git', 'api.github.com', 'user/repo'],
            ['http://github.com/user/repo.git', 'api.github.com', 'user/repo'],
            ['https://github.com/user/repo.git', 'api.github.com', 'user/repo'],
            ['git@example.org:user/repo.git', 'example.org', 'user/repo'],
            ['http://example.org/user/repo.git', 'example.org', 'user/repo'],
            ['https://example.org/user/repo.git', 'example.org', 'user/repo'],
        ];
    }
}
