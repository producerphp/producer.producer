# CHANGELOG

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

