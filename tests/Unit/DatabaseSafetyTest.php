<?php

use Illuminate\Support\Facades\DB;

it('runs against an in memory sqlite database', function () {
    expect(config('database.default'))->toBe('testing');
    expect(config('database.connections.testing.driver'))->toBe('sqlite');
    expect(config('database.connections.testing.database'))->toBe(':memory:');
});

it('never points the test suite at a real database server', function () {
    $connection = DB::connection();

    expect($connection->getDriverName())->toBe('sqlite');
    expect($connection->getDatabaseName())->toBe(':memory:');
});
