<?php
declare(strict_types=1);

namespace Producer;

use Producer\Fsio\HomeFsio;
use Producer\Fsio\RepoFsio;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    protected function mockHomeFsio(array $returnData, $isFile = true)
    {
        $homefs = $this->createMock(HomeFsio::class);

        $homefs->expects($this->any())
            ->method('isFile')->will($this->returnValue($isFile));

        $homefs->expects($this->any())
            ->method('parseIni')->will($this->returnValue($returnData));

        return $homefs;
    }

    protected function mockRepoFsio(array $returnData, $isFile = true)
    {
        $repofs = $this->createMock(RepoFsio::class);

        $repofs->expects($this->any())
            ->method('isFile')->will($this->returnValue($isFile));

        $repofs->expects($this->any())
            ->method('parseIni')->will($this->returnValue($returnData));

        return $repofs;
    }

    public function testLoadHomeConfig() : void
    {
        $homefs = $this->mockHomeFsio([
            'gitlab_token' => 'foobarbazdibzimgir',
            'commands' => [
                'phpunit' => '/path/to/phpunit',
            ]
        ]);

        $repofs = $this->mockRepoFsio([], false);

        $config = new Config($homefs, $repofs);

        $expect = [
            'bitbucket_hostname' => 'api.bitbucket.org',
            'bitbucket_username' => null,
            'bitbucket_password' => null,
            'github_hostname' => 'api.github.com',
            'github_username' => null,
            'github_token' => null,
            'gitlab_hostname' => 'gitlab.com',
            'gitlab_token' => 'foobarbazdibzimgir',
            'package' => '',
            'commands' => [
                'phpdoc' => 'phpdoc',
                'phpunit' => '/path/to/phpunit',
            ],
            'files' => [
                'changes' => 'CHANGES.md',
                'contributing' => 'CONTRIBUTING.md',
                'license' => 'LICENSE.md',
                'phpunit' => 'phpunit.xml.dist',
                'readme' => 'README.md',
            ],
        ];

        $actual = $config->getAll();

        $this->assertSame($expect, $actual);
    }

    public function testGitHubHostOverride() : void
    {
        $homefs = $this->mockHomeFsio([
            'github_hostname' => 'example.org',
            'github_username' => 'foo',
            'github_token' => 'bar',
        ]);

        $repofs = $this->mockRepoFsio([], false);

        $config = new Config($homefs, $repofs);

        $expect = [
            'bitbucket_hostname' => 'api.bitbucket.org',
            'bitbucket_username' => null,
            'bitbucket_password' => null,
            'github_hostname' => 'example.org',
            'github_username' => 'foo',
            'github_token' => 'bar',
            'gitlab_hostname' => 'gitlab.com',
            'gitlab_token' => null,
            'package' => '',
            'commands' => [
                'phpdoc' => 'phpdoc',
                'phpunit' => 'phpunit',
            ],
            'files' => [
                'changes' => 'CHANGES.md',
                'contributing' => 'CONTRIBUTING.md',
                'license' => 'LICENSE.md',
                'phpunit' => 'phpunit.xml.dist',
                'readme' => 'README.md',
            ],
        ];

        $actual = $config->getAll();

        $this->assertSame($expect, $actual);
    }

    public function testGitlabHostOverride() : void
    {
        $homefs = $this->mockHomeFsio([
            'gitlab_hostname' => 'example.org',
            'gitlab_token' => 'bar',
        ]);

        $repofs = $this->mockRepoFsio([], false);

        $config = new Config($homefs, $repofs);

        $expect = [
            'bitbucket_hostname' => 'api.bitbucket.org',
            'bitbucket_username' => null,
            'bitbucket_password' => null,
            'github_hostname' => 'api.github.com',
            'github_username' => null,
            'github_token' => null,
            'gitlab_hostname' => 'example.org',
            'gitlab_token' => 'bar',
            'package' => '',
            'commands' => [
                'phpdoc' => 'phpdoc',
                'phpunit' => 'phpunit',
            ],
            'files' => [
                'changes' => 'CHANGES.md',
                'contributing' => 'CONTRIBUTING.md',
                'license' => 'LICENSE.md',
                'phpunit' => 'phpunit.xml.dist',
                'readme' => 'README.md',
            ],
        ];

        $actual = $config->getAll();

        $this->assertSame($expect, $actual);
    }

    public function testLoadHomeAndRepoConfig() : void
    {
        $homefs = $this->mockHomeFsio(['gitlab_token' => 'foobarbazdibzimgir']);

        $repofs = $this->mockRepoFsio([
            'package' => 'Foo.Bar',
            'commands' => [
                'phpunit' => './vendor/bin/phpunit'
            ],
            'files' => [
                'contributing' => '.github/CONTRIBUTING'
            ],
        ]);

        $config = new Config($homefs, $repofs);

        $expect = [
            'bitbucket_hostname' => 'api.bitbucket.org',
            'bitbucket_username' => null,
            'bitbucket_password' => null,
            'github_hostname' => 'api.github.com',
            'github_username' => null,
            'github_token' => null,
            'gitlab_hostname' => 'gitlab.com',
            'gitlab_token' => 'foobarbazdibzimgir',
            'package' => 'Foo.Bar',
            'commands' => [
                'phpdoc' => 'phpdoc',
                'phpunit' => './vendor/bin/phpunit',
            ],
            'files' => [
                'changes' => 'CHANGES.md',
                'contributing' => '.github/CONTRIBUTING',
                'license' => 'LICENSE.md',
                'phpunit' => 'phpunit.xml.dist',
                'readme' => 'README.md',
            ],
        ];

        $actual = $config->getAll();

        $this->assertSame($expect, $actual);
    }
}
