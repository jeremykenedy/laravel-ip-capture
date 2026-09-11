# Upgrading

## From 1.1 to 1.2

Upgrading is a composer update. No public method, config key, publish tag or
translation key was removed, and no default behaviour changed.

```bash
composer update jeremykenedy/laravel-ip-capture
```

Three things are worth reading before you deploy.

### The migration was renamed

The bundled migration is now
`2026_03_28_135324_add_ip_capture_columns_to_users_table.php`, named for when it
was written rather than the placeholder date it shipped with.

A migration file name is its identity in the `migrations` table, so an
application that already ran the old file sees an unrun migration after
upgrading. Running `php artisan migrate` is safe: every column is guarded by
`Schema::hasColumn`, so nothing is altered and only a bookkeeping row is added.

`down()` checks for the old file name in the `migrations` table and refuses to
drop the columns when it finds it, so a rollback cannot delete data that an
earlier version of the package created.

If you published the migration into your own `database/migrations`, your copy is
untouched and keeps its own name.

### New config keys

The published config file does not gain new keys on its own. Everything added in
1.2 has a default that matches the previous behaviour, so an older published
config keeps working as it did.

Republish when you want the new options documented in your own file:

```bash
php artisan vendor:publish --tag=ip-capture-config --force
```

That overwrites `config/ip-capture.php`, so note your current values first.

### The enabled option now works

`config('ip-capture.enabled')` was documented as the global switch but nothing
read it. It is now honoured: when it is false, every capture resolves to
`null_ip` and no column is written.

The default is true, so nothing changes unless you had already set it to false
and expected it to take effect.

## From 1.0 to 1.1

See the [release notes](https://github.com/jeremykenedy/laravel-ip-capture/releases).
