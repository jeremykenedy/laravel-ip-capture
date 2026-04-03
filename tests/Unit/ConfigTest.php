<?php

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
