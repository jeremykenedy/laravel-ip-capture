<?php

use Jeremykenedy\LaravelIpCapture\Contracts\IpResolverInterface;
use Jeremykenedy\LaravelIpCapture\Services\IpResolver;
use Illuminate\Http\Request;

it('resolves the ip resolver from the container', function () {
    $resolver = app(IpResolverInterface::class);
    expect($resolver)->toBeInstanceOf(IpResolver::class);
});

it('returns an ip address string', function () {
    $resolver = app(IpResolverInterface::class);
    $ip = $resolver->getClientIp();
    expect($ip)->toBeString();
});

it('returns null ip when no request ip available', function () {
    $request = Request::create('/test', 'GET', [], [], [], [
        'REMOTE_ADDR' => null,
    ]);
    $this->app->instance('request', $request);

    $resolver = new IpResolver($request);
    $ip = $resolver->getClientIp();

    expect($ip)->toBeString();
});

it('returns the request ip address', function () {
    $request = Request::create('/test', 'GET', [], [], [], [
        'REMOTE_ADDR' => '192.168.1.100',
    ]);
    $this->app->instance('request', $request);

    $resolver = new IpResolver($request);
    $ip = $resolver->getClientIp();

    expect($ip)->toBe('192.168.1.100');
});

it('hashes the ip when hash is enabled', function () {
    config(['ip-capture.hash' => true, 'ip-capture.hash_algo' => 'sha256']);

    $request = Request::create('/test', 'GET', [], [], [], [
        'REMOTE_ADDR' => '192.168.1.100',
    ]);

    $resolver = new IpResolver($request);
    $ip = $resolver->getClientIp();

    expect($ip)->toBe(hash('sha256', '192.168.1.100'));
    expect($ip)->not->toBe('192.168.1.100');
});

it('reads cloudflare connecting ip header', function () {
    $request = Request::create('/test', 'GET', [], [], [], [
        'HTTP_CF_CONNECTING_IP' => '203.0.113.50',
        'REMOTE_ADDR' => '127.0.0.1',
    ]);

    config(['ip-capture.trust_proxies' => false]);

    $resolver = new IpResolver($request);
    $ip = $resolver->getClientIp();

    expect($ip)->toBe('203.0.113.50');
});

it('reads x-forwarded-for header with multiple ips', function () {
    $request = Request::create('/test', 'GET', [], [], [], [
        'HTTP_X_FORWARDED_FOR' => '203.0.113.50, 70.41.3.18, 150.172.238.178',
        'REMOTE_ADDR' => '127.0.0.1',
    ]);

    config(['ip-capture.trust_proxies' => false]);

    $resolver = new IpResolver($request);
    $ip = $resolver->getClientIp();

    expect($ip)->toBe('203.0.113.50');
});
