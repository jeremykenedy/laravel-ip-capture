# Changelog

All notable changes to this package are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this package follows
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Blade components `<x-ip-capture::ip-table />` and `<x-ip-capture::ip-badge />`, with markup for Tailwind CSS v4, Bootstrap 5 and Bootstrap 4.
- Vue, React and Svelte `IpTable` components, publishable with the `ip-capture-js` tag. Each one carries a class map for all three CSS frameworks.
- A publishable Livewire component and view, `ip-capture-livewire`, which needs no Livewire dependency in the package itself.
- `ip-capture:install`, `ip-capture:update` and `ip-capture:switch` commands. Passing both `--css` and `--frontend` skips every prompt.
- Optional automatic capture on Eloquent model events, off by default, configured under `auto_capture`. Only events that fire before the row is written are accepted, since capture assigns an attribute.
- `hash_salt` and `anonymize` options. Both default to off, so stored values are unchanged for existing installs.
- Configurable `headers`, `table`, `after_column` and `column_length`.
- `display.mask` and `display.empty_label` for the shipped components.
- Publish tags `ip-capture-views`, `ip-capture-views-tailwind`, `ip-capture-views-bootstrap5`, `ip-capture-views-bootstrap4`, `ip-capture-js` and `ip-capture-livewire`.
- `Support\IpCapture` as the single resolution point for package configuration.
- PHPStan at level 6, plus CI jobs for static analysis, coverage with an 80 percent floor against a measured 83.4 percent, composer validation, dependency audit and frontend component checks.
- Dependabot configuration for composer and GitHub Actions.

### Changed

- `setIpColumn()` takes an optional second argument so an address can be supplied instead of resolved. A supplied address goes through the same anonymizing and hashing as a resolved one.
- `Support\IpCapture::prepare()` is the single place the storage pipeline is applied, so the resolver and the trait cannot drift apart.
- The bundled migration reads its table, placement and column length from config, skips a table that is not there, and creates any extra enabled column.
- The migration file was renamed from `2025_01_01_000000_add_ip_capture_columns_to_users_table.php` to `2026_03_28_135324_add_ip_capture_columns_to_users_table.php`, which is when it was written. Its `down()` refuses to drop the columns when the old file name is already recorded in the migrations table, so an install that predates 1.2 cannot lose data on rollback.
- The test matrix covers Laravel 10, 11, 12 and 13 against PHP 8.2, 8.3 and 8.4. Laravel 10 and 11 install with advisory blocking off, because every release of both now carries a security advisory upstream.
- Test suite grew from 31 tests to 237, including a test that locks the public API surface.

### Fixed

- `enabled` is now read. It was documented as the global switch but nothing acted on it, so setting it to false had no effect. It gates the column writes themselves, not just the resolver, so a disabled capture writes nothing rather than writing the null IP.
- Automatic capture compares against the stored form of the null IP, so with hashing on it no longer overwrites a real address with the digest of `0.0.0.0`.
- `down()` uses the shipped column list rather than the configured one, so a configuration edited after the migration ran cannot leave a column behind or drop one the migration never created.
- An empty `columns` array is honoured as enabling none, instead of being treated as absent configuration and creating all six.
- The installer validates `--css` and `--frontend` in non interactive mode even when only one of them is given.
- An unsupported `hash_algo` raises an `InvalidArgumentException` naming the value and config key instead of letting the opaque `ValueError` from `hash()` escape. An algorithm named in uppercase is still accepted, as `hash()` has always matched the name case insensitively.
- A proxy header holding something that is not an address no longer stops the lookup, so the next header in the list is tried.
- The README no longer claims automatic capture happens on its own. It is opt in, and now it exists.

## Releases before this entry

Versions 1.0.0 through 1.1.0 predate this file. See the
[release history](https://github.com/jeremykenedy/laravel-ip-capture/releases).
