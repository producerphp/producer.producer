Second major release.

- Supports package-level installation (in addition to global installation).

- Supports package-specific configuration file at `.producer/config`, allowing you to specify the `@package` name in docblocks, the `phpunit` and `phpdoc` command paths, and the names of the various support files.

- No longer installs `phpunit` and `phpdoc`; you will need to install them yourself, either globally or as part of your package.

- Reorganized internals to split out HTTP interactions.

- Updated instructions and tests.
