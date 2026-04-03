<?php

use Jeremykenedy\LaravelIpCapture\Contracts\IpResolverInterface;
use Jeremykenedy\LaravelIpCapture\Providers\IpCaptureServiceProvider;
use Jeremykenedy\LaravelIpCapture\Services\IpResolver;

it('registers the service provider', function () {
    $providers = app()->getLoadedProviders();
    expect($providers)->toHaveKey(IpCaptureServiceProvider::class);
});

it('binds ip resolver interface to concrete class', function () {
    $resolver = app(IpResolverInterface::class);
    expect($resolver)->toBeInstanceOf(IpResolver::class);
});

it('merges config from package', function () {
    expect(config('ip-capture'))->toBeArray();
    expect(config('ip-capture'))->toHaveKeys([
        'enabled',
        'null_ip',
        'columns',
        'trust_proxies',
        'hash',
        'hash_algo',
    ]);
});

it('loads translations', function () {
    expect(trans('ip-capture::ip-capture.captured'))->toBe('IP address captured successfully.');
    expect(trans('ip-capture::ip-capture.label_ip'))->toBe('IP Address');
    expect(trans('ip-capture::ip-capture.label_date'))->toBe('Captured At');
    expect(trans('ip-capture::ip-capture.not_found'))->toBe('IP address not found.');
});
