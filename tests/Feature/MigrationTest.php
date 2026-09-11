<?php

use Illuminate\Support\Facades\Schema;
use Jeremykenedy\LaravelIpCapture\Support\IpCapture;

it('adds every shipped column to the users table', function () {
    $this->createUsersTable();

    $this->artisan('migrate')->assertSuccessful();

    foreach (IpCapture::DEFAULT_COLUMNS as $column) {
        expect(Schema::hasColumn('users', $column))->toBeTrue();
    }
});

it('leaves the columns nullable so existing rows survive the migration', function () {
    $this->createUsersTable();

    $this->artisan('migrate')->assertSuccessful();

    $id = DB::table('users')->insertGetId(['name' => 'no ip recorded']);

    expect(DB::table('users')->where('id', $id)->value('signup_ip_address'))->toBeNull();
});

it('skips a column that is disabled in config', function () {
    config(['ip-capture.columns.signup_sm_ip_address' => false]);

    $this->createUsersTable();

    $this->artisan('migrate')->assertSuccessful();

    expect(Schema::hasColumn('users', 'signup_sm_ip_address'))->toBeFalse()
        ->and(Schema::hasColumn('users', 'signup_ip_address'))->toBeTrue();
});

it('adds a column that was added to config', function () {
    config(['ip-capture.columns.reset_ip_address' => true]);

    $this->createUsersTable();

    $this->artisan('migrate')->assertSuccessful();

    expect(Schema::hasColumn('users', 'reset_ip_address'))->toBeTrue();
});

it('writes to the table named in config', function () {
    config(['ip-capture.table' => 'members']);

    $this->createUsersTable('members');

    $this->artisan('migrate')->assertSuccessful();

    expect(Schema::hasColumn('members', 'signup_ip_address'))->toBeTrue();
});

it('does nothing when the configured table is not there', function () {
    config(['ip-capture.table' => 'a_table_that_does_not_exist']);

    $this->artisan('migrate')->assertSuccessful();

    expect(Schema::hasTable('a_table_that_does_not_exist'))->toBeFalse();
});

it('leaves a column another package already created alone', function () {
    $this->createUsersTable();
    Schema::table('users', function ($table) {
        $table->string('signup_ip_address', 45)->nullable();
    });

    $this->artisan('migrate')->assertSuccessful();

    expect(Schema::hasColumn('users', 'signup_ip_address'))->toBeTrue()
        ->and(Schema::hasColumn('users', 'admin_ip_address'))->toBeTrue();
});

it('drops the columns again on rollback', function () {
    $this->createUsersTable();

    $this->artisan('migrate')->assertSuccessful();
    $this->artisan('migrate:rollback')->assertSuccessful();

    foreach (IpCapture::DEFAULT_COLUMNS as $column) {
        expect(Schema::hasColumn('users', $column))->toBeFalse();
    }

    expect(Schema::hasTable('users'))->toBeTrue();
});

it('refuses to drop columns a pre 1.2 install already applied', function () {
    $this->createUsersTable();

    $this->artisan('migrate')->assertSuccessful();

    // Stand in for an application that ran the migration under its old file
    // name, where these columns predate the renamed migration.
    DB::table('migrations')->insert([
        'migration' => '2025_01_01_000000_add_ip_capture_columns_to_users_table',
        'batch'     => 1,
    ]);

    $this->artisan('migrate:rollback')->assertSuccessful();

    foreach (IpCapture::DEFAULT_COLUMNS as $column) {
        expect(Schema::hasColumn('users', $column))->toBeTrue();
    }
});

it('stores an address of the configured length', function () {
    config(['ip-capture.column_length' => 128, 'ip-capture.hash' => true, 'ip-capture.hash_algo' => 'sha512']);

    $this->createUsersTable();

    $this->artisan('migrate')->assertSuccessful();

    $digest = hash('sha512', '203.0.113.9');
    $id = DB::table('users')->insertGetId(['signup_ip_address' => $digest]);

    expect(DB::table('users')->where('id', $id)->value('signup_ip_address'))->toBe($digest);
});

it('adds no columns when config enables none', function () {
    config(['ip-capture.columns' => []]);

    $this->createUsersTable();

    $this->artisan('migrate')->assertSuccessful();

    foreach (IpCapture::DEFAULT_COLUMNS as $column) {
        expect(Schema::hasColumn('users', $column))->toBeFalse();
    }
});

it('rolls back the shipped columns even after config changed since the migration ran', function () {
    $this->createUsersTable();

    $this->artisan('migrate')->assertSuccessful();

    // The application disables a column after migrating. Rollback still has to
    // clear what the migration created.
    config(['ip-capture.columns.signup_ip_address' => false]);

    $this->artisan('migrate:rollback')->assertSuccessful();

    foreach (IpCapture::DEFAULT_COLUMNS as $column) {
        expect(Schema::hasColumn('users', $column))->toBeFalse();
    }
});

it('leaves a custom column alone on rollback', function () {
    config(['ip-capture.columns.reset_ip_address' => true]);

    $this->createUsersTable();

    $this->artisan('migrate')->assertSuccessful();
    expect(Schema::hasColumn('users', 'reset_ip_address'))->toBeTrue();

    $this->artisan('migrate:rollback')->assertSuccessful();

    expect(Schema::hasColumn('users', 'reset_ip_address'))->toBeTrue()
        ->and(Schema::hasColumn('users', 'signup_ip_address'))->toBeFalse();
});

it('adds the columns to a table that has no column to sit after', function () {
    config(['ip-capture.table' => 'visits', 'ip-capture.after_column' => 'password']);

    Schema::create('visits', function ($table) {
        $table->id();
        $table->string('referrer')->nullable();
    });

    $this->artisan('migrate')->assertSuccessful();

    foreach (IpCapture::DEFAULT_COLUMNS as $column) {
        expect(Schema::hasColumn('visits', $column))->toBeTrue();
    }
});

it('sizes the columns for a sha512 digest without being told to', function () {
    config(['ip-capture.hash' => true, 'ip-capture.hash_algo' => 'sha512']);

    $this->createUsersTable();

    $this->artisan('migrate')->assertSuccessful();

    $digest = hash('sha512', '203.0.113.9');
    $id = DB::table('users')->insertGetId(['signup_ip_address' => $digest]);

    expect(strlen($digest))->toBe(128)
        ->and(DB::table('users')->where('id', $id)->value('signup_ip_address'))->toBe($digest);
});
