<?php

use Jeremykenedy\LaravelIpCapture\Support\IpCapture;

it('has default config values', function () {
    expect(config('ip-capture.enabled'))->toBeTrue();
    expect(config('ip-capture.null_ip'))->toBe('0.0.0.0');
    expect(config('ip-capture.trust_proxies'))->toBeTrue();
    expect(config('ip-capture.hash'))->toBeFalse();
    expect(config('ip-capture.hash_algo'))->toBe('sha256');
});

it('has all default columns enabled', function () {
    $columns = config('ip-capture.columns');

    expect($columns)->toBeArray();
    expect($columns)->toHaveCount(6);
    expect($columns['signup_ip_address'])->toBeTrue();
    expect($columns['signup_confirmation_ip_address'])->toBeTrue();
    expect($columns['signup_sm_ip_address'])->toBeTrue();
    expect($columns['admin_ip_address'])->toBeTrue();
    expect($columns['updated_ip_address'])->toBeTrue();
    expect($columns['deleted_ip_address'])->toBeTrue();
});

it('can override config values at runtime', function () {
    config(['ip-capture.enabled' => false]);
    expect(config('ip-capture.enabled'))->toBeFalse();

    config(['ip-capture.null_ip' => '127.0.0.1']);
    expect(config('ip-capture.null_ip'))->toBe('127.0.0.1');
});

it('can disable individual columns', function () {
    config(['ip-capture.columns.signup_ip_address' => false]);
    expect(config('ip-capture.columns.signup_ip_address'))->toBeFalse();
    expect(config('ip-capture.columns.admin_ip_address'))->toBeTrue();
});

it('resolves config via dot notation', function () {
    expect(config('ip-capture'))->toBeArray();
    expect(config('ip-capture.columns'))->toBeArray();
    expect(config('ip-capture.columns.signup_ip_address'))->toBeBool();
});

it('ships the privacy options switched off', function () {
    expect(config('ip-capture.hash_salt'))->toBe('');
    expect(config('ip-capture.anonymize'))->toBeFalse();
});

it('ships automatic capture switched off', function () {
    expect(config('ip-capture.auto_capture.enabled'))->toBeFalse();
});

it('ships database defaults that match the bundled migration', function () {
    expect(config('ip-capture.table'))->toBe('users');
    expect(config('ip-capture.after_column'))->toBe('password');
    expect(config('ip-capture.column_length'))->toBe(64);
});

it('ships the proxy header list in priority order', function () {
    expect(config('ip-capture.headers'))->toBe([
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR',
    ]);
});

it('ships tailwind and blade as the effective frontend defaults', function () {
    // The keys themselves ship unset so a laravel-ui-kit setting can be
    // inherited. The accessors are what the package reads.
    expect(IpCapture::cssFramework())->toBe('tailwind');
    expect(IpCapture::frontend())->toBe('blade');
    expect(config('ip-capture.views.enabled'))->toBeTrue();
});
