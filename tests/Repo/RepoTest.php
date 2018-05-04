<?php
namespace Producer\Repo;

use Producer\Config;
use Producer\Fsio;
use Producer\Stdlog;

class FakeRepo extends AbstractRepo
{
    public function setOrigin()
    {
        $this->origin = 'FAKE';
    }

    public function getBranch()
    {

    }

    public function checkStatus()
    {

    }

    public function tag($name, $message)
    {

    }

    public function sync()
    {

    }
}

class RepoTest extends \PHPUnit\Framework\TestCase
{
    protected function mockFsio($text)
    {
        $fsio = $this->getMockBuilder(Fsio::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $fsio->expects($this->any())
            ->method('get')
            ->will($this->returnValue($text));

        return $fsio;
    }

    protected function mockConfig(array $files)
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $config->expects($this->any())
            ->method('get')
            ->will($this->returnValue($files));

        return $config;
    }

    public function testGetChanges()
    {
        $fsio = $this->mockFsio($this->changelog);
        $logger = new Stdlog(STDOUT, STDERR);
        $config = $this->mockConfig([
            'changes' => 'CHANGES.md',
        ]);
        $repo = new FakeRepo($fsio, $logger, $config);
        $actual = $repo->getChanges();
        $expect = $this->changelog;
        $this->assertSame($expect, $actual);

        $config = $this->mockConfig([
            'changes' => 'CHANGELOG.md',
        ]);
        $repo = new FakeRepo($fsio, $logger, $config);
        $actual = $repo->getChanges();
        $expect = trim($this->subset);
        $this->assertSame($expect, $actual);
    }

    protected $subset = "
- Added `--no-docs` option to suppress running PHPDocumentor; this is for
  projects release versions using PHP 7.1 nullable types, which PHPDocumentor
  does not support yet.

- Renamed CHANGES.md to CHANGELOG.md; now compliant with `pds/skeleton`.
";

    protected $changelog = "# CHANGELOG

## 2.2.0

- Added `--no-docs` option to suppress running PHPDocumentor; this is for
  projects release versions using PHP 7.1 nullable types, which PHPDocumentor
  does not support yet.

- Renamed CHANGES.md to CHANGELOG.md; now compliant with `pds/skeleton`.

## 2.1.0

- Add support for GitHub Enterprise, self-hosted GitLab, and Bitbucket Server
via `*_hostname` config directives.

- The CHANGES file is now checked for existence *last*, so that those without
a CHANGES file can update it once at the very end of the validation process.

- Added a README note that Producer supports testing systems other than PHPUnit.

## 2.0.0

Second major release.

- Supports package-level installation (in addition to global installation).

- Supports package-specific configuration file at `.producer/config`, allowing you to specify the `@package` name in docblocks, the `phpunit` and `phpdoc` command paths, and the names of the various support files.

- No longer installs `phpunit` and `phpdoc`; you will need to install them yourself, either globally or as part of your package.

- Reorganized internals to split out HTTP interactions.

- Updated instructions and tests.

## 1.0.0

First major release.

";
}
