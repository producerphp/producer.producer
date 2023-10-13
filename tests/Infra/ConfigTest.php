<?php
declare(strict_types=1);

namespace Producer\Infra;

use Producer\Infra\Fsio\HomeFsio;
use Producer\Infra\Fsio\RepoFsio;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param mixed[] $returnData
     */
    protected function mockHomeFsio(array $returnData, bool $isFile = true) : HomeFsio
    {
        $homefs = $this->createMock(HomeFsio::class);
        $homefs
            ->expects($this->any())
            ->method('isFile')
            ->will($this->returnValue($isFile));
        $homefs
            ->expects($this->any())
            ->method('parseIni')
            ->will($this->returnValue($returnData));
        return $homefs;
    }

    /**
     * @param mixed[] $returnData
     */
    protected function mockRepoFsio(array $returnData, bool $isFile = true) : RepoFsio
    {
        $repofs = $this->createMock(RepoFsio::class);
        $repofs
            ->expects($this->any())
            ->method('isFile')
            ->will($this->returnValue($isFile));
        $repofs
            ->expects($this->any())
            ->method('parseIni')
            ->will($this->returnValue($returnData));
        return $repofs;
    }

    public function testLoadHomeConfig() : void
    {
        $homefs = $this->mockHomeFsio(['gitlab_token' => 'foobarbazdibzimgir']);
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
            'quality_command' => null,
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
            'quality_command' => null,
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
            'quality_command' => null,
        ];
        $actual = $config->getAll();
        $this->assertSame($expect, $actual);
    }

    public function testLoadHomeAndRepoConfig() : void
    {
        $homefs = $this->mockHomeFsio(['gitlab_token' => 'foobarbazdibzimgir']);
        $repofs = $this->mockRepoFsio([]);
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
            'quality_command' => null,
        ];
        $actual = $config->getAll();
        $this->assertSame($expect, $actual);
    }
}
