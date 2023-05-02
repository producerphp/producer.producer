# TODO

## General

- `validate`

    - check previous major/minor/bugfix version numbers

- when `changes = CHANGELOG.*`, to get the change notes for the release, regex
  to find a line with `## VERSION` on it, up til the next `## *` heading.

- allow an "api" config directive that says "github", "gitlab", or "bitbucket"
  so we don't depend on a particular URL

## New Commands

Cf. existing aura bin commands at <https://github.com/auraphp/bin/tree/master/src/Command>.

```
producer versions
    lists existing release versions

producer log-since {$version}
```
