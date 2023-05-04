# CHANGELOG

## (UNRELEASED) 3.0.0

### Internal changes

- Declares strict_types throughout
- Separate Fsio classes for Home and Repo
- Uses pmjones/caplet for a DI container
- Uses pmjones/AutoShell for the command-line interface
- PHPStan applied at max level throughout
- Only requires CHANGELOG and LICENSE files
- Modified order of `validate` steps
- Major reorg of namespaces and src/ directory into Application Layer (`App`), Infrastructure Layer (`Infra`), and Presentation Layer (`Sapi`)

### User-visible changes

- Requires PHP 8.1 or later
- No longer checks docblocks or unit tests; instead, runs a single configurable quality-check command, default `composer check`
- No more `release` command; instead, `validate <version> --release`
- New command `log` to show log of changes since last release
- `validate` requires that CHANGELOG have a heading for the version being validated
- No longer honors `files` overrides; files must be named per pds/skeleton standard

### Upgrade notes

- No changes needed to .producer/config files, though files, phpunit, and phpdoc values will now be ignored.

- Must rename your root-level files to match pds/skeleton standard

- Instead of `producer release <version>`, use `producer validate <version> --release`

- Must have either a `composer check` script to run your QA checks (e.g. PHPUnit), or set a `quality_command` value in .producer/config  to run QA checks.
