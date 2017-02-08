# TODO

## General

- `validate`

    - check previous major/minor/bugfix version numbers

    - check for @license tag in file-level docblock

- getting the change notes for the release, regex CHANGELOG to find a line with
  `## VERSION` on it, up til the next `## *` heading.

- allow an "api" config directive that says "github", "gitlab", or "bitbucket"
  so we don't depend on a particular URL

- consider allowing `[vendor/package]` groups in `~/.producer/config` to have
  specific configs for specific packages, so that credentials have no chance of
  making it into the package.

- If `phpunit.xml.*` is not present, do not try to run PHPUnit tests.

## New Commands

Cf. existing aura bin commands at <https://github.com/auraphp/bin/tree/master/src/Command>.

```
producer versions
    lists existing release versions

producer log-since {$version}
```
