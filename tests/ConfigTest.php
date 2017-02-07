<?php
namespace Producer;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected function mockFsio(array $returnData, $isFile = true)
    {
        $fsio = $this->getMockBuilder(Fsio::class)
            ->disableOriginalConstructor()
            ->setMethods(['isFile', 'parseIni'])
            ->getMock();
        $fsio
            ->expects($this->any())
            ->method('isFile')->will($this->returnValue($isFile));
        $fsio
            ->expects($this->any())
            ->method('parseIni')->will($this->returnValue($returnData));

        return $fsio;
    }

    public function testLoadHomeConfig()
    {
        $homefs = $this->mockFsio([
            'gitlab_token' => 'foobarbazdibzimgir',
        ]);
        $repofs = $this->mockFsio([], false);

        $config = new Config($homefs, $repofs);

        $expect = [
            'bitbucket_password' => null,
            'bitbucket_username' => null,
            'github_token' => null,
            'github_username' => null,
            'gitlab_token' => 'foobarbazdibzimgir',
        ];

        $actual = $config->getAll();

        $this->assertSame($expect, $actual);
    }

    public function testLoadHomeAndRepoConfig()
    {
        $homefs = $this->mockFsio(['gitlab_token' => 'foobarbaz']);
        $repofs = $this->mockFsio(['gitlab_token' => 'dibzimgir']);

        $config = new Config($homefs, $repofs);

        $expect = [
            'bitbucket_password' => null,
            'bitbucket_username' => null,
            'github_token' => null,
            'github_username' => null,
            'gitlab_token' => 'dibzimgir',
        ];

        $actual = $config->getAll();

        $this->assertSame($expect, $actual);
    }
}
