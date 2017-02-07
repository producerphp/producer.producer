# Change Log

## Unreleased

- File names are no longer configurable; they must conform to `pds/skeleton`.

- The package name is no longer configurable; it must be the same as in
  `composer.json`.

- The command for PHPDocumentor is no longer configurable. Instead, Producer
  searches for `./vendor/bin/phpdoc`, then falls back to a system-wide `phpdoc`
  if not found in `./vendor/bin`.

- The command for PHPUnit is no longer configurable. Instead, Producer searches
  for `./vendor/bin/phpunit`, then falls back to a system-wide `phpunit` if not
  found in `./vendor/bin`.

## 2.0.0

Second major release.

- Supports package-level installation (in addition to global installation).

- Supports package-specific configuration file at `.producer/config`, allowing you to specify the `@package` name in docblocks, the `phpunit` and `phpdoc` command paths, and the names of the various support files.

- No longer installs `phpunit` and `phpdoc`; you will need to install them yourself, either globally or as part of your package.

- Reorganized internals to split out HTTP interactions.

- Updated instructions and tests.

## 1.0.0

First major release.
