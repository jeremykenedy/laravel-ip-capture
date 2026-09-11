# Contributing

Thanks for taking the time to contribute.

## Getting set up

```bash
composer install
```

## Before opening a pull request

```bash
composer check
```

That runs the three gates CI runs:

```bash
./vendor/bin/pint --test      # code style
./vendor/bin/phpstan analyse  # static analysis, level 6
./vendor/bin/pest --ci        # test suite
```

Run `./vendor/bin/pint` to fix style automatically. StyleCI runs alongside Pint
on this repository, so leave `pint.json` alone unless the two are in conflict.

## Conventions

- The package supports Laravel 10 through 13 and PHP 8.2 or newer. Anything you
  add has to work on the oldest of each.
- `Support\IpCapture` is the only place package configuration is read. Add a
  method there rather than calling `config()` from a new class.
- All CSS specific markup lives in `resources/views/{framework}/blade`. Component
  classes stay framework agnostic.
- A change to a shipped view needs the same change in all three framework
  directories, and the frontend CI job checks all three exist.

## Backwards compatibility

This package is installed by other applications, so the public surface is fixed
within a major version: the `CapturesIp` methods, `IpResolverInterface`,
`IpResolver`, the service provider name, config keys, publish tags, translation
keys and the migration file name.

`tests/Unit/BackwardCompatibilityTest.php` asserts all of it. If a change makes
that file fail, the change needs a major version rather than a new assertion.

## Tests

Every test runs against SQLite in memory, which
`tests/Unit/DatabaseSafetyTest.php` enforces. Tests must not reach a real
database, the network or the filesystem outside the test skeleton.
