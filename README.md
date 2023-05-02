# Producer

Producer is a command-line quality-assurance tool to validate, and then release,
your PHP library package. It supports Git and Mercurial for version control, as
well as Github, Gitlab, and Bitbucket for remote origins (including self-hosted
origins).

## Installing

Producer works in concert with [Composer][], [PHPUnit][], and [PHPDocumentor][].
Please install them first, either as part of your global system, or as part of
your package.

[Composer]: https://getcomposer.org
[PHPUnit]: https://packagist.org/packages/phpunit/phpunit
[PHPDocumentor]: https://packagist.org/packages/phpdocumentor/phpdocumentor

### Global Install

To install Producer globally, issue `composer global require producer/producer`.

Be sure to add `$COMPOSER_HOME/vendor/bin` to your `$PATH`;
[instuctions here](https://getcomposer.org/doc/03-cli.md#global).

Test the installation by issuing `producer` at the command line to see some
"help" output.

> Remember, you will need [PHPUnit][] and [PHPDocumentor][] as well.

### Package Install

To install the Producer package as a development requirement for your package issue `composer require --dev producer/producer`.

Test the installation by issuing `./vendor/bin/producer` at the command line to
see some "help" output.

> Remember, you will need [PHPUnit][] and [PHPDocumentor][] as well.

## Configuring

Before you get going, you'll need to create a `~/.producer/config` file. Copy
and paste the following into your terminal:

```
mkdir ~/.producer

echo "; Github
github_username =
github_token =

; Gitlab
gitlab_token =

; Bitbucket
bitbucket_username =
bitbucket_password =" > ~/.producer/config
```

You can then edit `~/.producer/config` to enter your access credentials, any or
all of:

- [Github personal API token](https://github.com/settings/tokens),
- [Gitlab private token](https://gitlab.com/profile/account), or
- Bitbucket username and password.

> WARNING: Saving your username and password for Bitbucket in plain text is not
> very secure. Bitbucket doesn't have personal API tokens, so it's either
> "username and password" or bring in an external OAuth1 library with all its
> dependencies just to authenticate to Bitbucket. The latter option might show
> up in a subsequent release.

### Package Configuration

Inside your package repository, you may define a `.producer/config` file that
sets any of the following options for that specific package.

```ini
; custom hostnames for self-hosted origins
github_hostname = example.com
gitlab_hostname = example.net
bitbucket_hostname = example.org

; commands to use for phpunit and phpdoc
[commands]
phpunit = /path/to/phpunit
phpdoc = /path/to/phpdoc

; names for support files
[files]
changes = CHANGES.md
contributing = CONTRIBUTING.md
license = LICENSE.md
phpunit = phpunit.xml.dist
readme = README.md
```

> **Testing Systems**: If you want to use a testing system other than PHPUnit,
> you can set `phpunit = /whatever/you/want`. As long as it exits non-zero when
> the tests fail, Producer will work with it properly. Yes, it was short-sighted
> to name the key `phpunit`; a future release of Producer may remedy that.

## Getting Started

Now that you have Producer installed and configured, change to the directory
for your library package repository. From there, you can call the following
commands:

- `producer issues` will show all the open issues from the remote origin
- `producer validate <version>` will validate the package for release, but won't
   actually release it
- `producer release <version>` will validate, and then actually release, the
  package

> NOTE: Producer reads the `.git` or `.hg` configuration data from the
> repository, so it knows whether you are using Github, Gitlab, or Bitbucket
> as the remote origin.

## Validating

When you validate the library package, Producer will:

- Sync with the remote origin (i.e., pull from the remote origin, then push any
  local changes, then check the local status to make sure everything is
  committed and pushed)
- Validate the composer.json file
- Check for informational files (see below) and for a `phpunit.xml.dist` file
- Check that the license file has the current year in it
- Call `composer update`, run the unit tests, and make sure they cleaned up after
- Check that the changes file is in the most-recent commit to the repository

If any of those fails, then the package is not considered valid for release.

In addition, the `validate` command will show any open issues from the remote
origin, but these are presented only as a reminder, and will not be considered
invalidators.

### Informational Files

Producer wants you to have these informational files in the package root:

- `CHANGES.md`, a list of changes for the release;
- `CONTRIBUTING.md`, describing how to contribute to the library;
- `LICENSE.md`, the package licensing text; and,
- `README.md`, an introduction to the library.

You may override these file names by setting the appropriate `.producer/config`
directives.

## Releasing

When you `release` the package, Producer will first `validate` it as a pre-flight step.

Then it will use the Github or Gitlab API to create a release. In the case of
Bitbucket (which does not have an API for releases) it will tag the repository
locally.

Finally, Producer will sync with the remote origin so that the release is
represented locally, and/or pushed to the remote.
