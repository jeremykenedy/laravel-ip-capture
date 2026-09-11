<?php

use Illuminate\Http\Request;
use Jeremykenedy\LaravelIpCapture\Contracts\IpResolverInterface;
use Jeremykenedy\LaravelIpCapture\Services\IpResolver;

function resolverFor(array $server = []): IpResolver
{
    $request = Request::create('/test', 'GET', [], [], [], $server);

    // Request::create always supplies a localhost REMOTE_ADDR, which hides the
    // case where nothing at all is known about the client.
    if (!array_key_exists('REMOTE_ADDR', $server)) {
        $request->server->remove('REMOTE_ADDR');
    }

    return new IpResolver($request);
}

it('resolves the ip resolver from the container', function () {
    expect(app(IpResolverInterface::class))->toBeInstanceOf(IpResolver::class);
});

it('returns the request ip address', function () {
    expect(resolverFor(['REMOTE_ADDR' => '192.168.1.100'])->getClientIp())->toBe('192.168.1.100');
});

it('returns the configured null ip when no address can be determined', function () {
    config(['ip-capture.trust_proxies' => false, 'ip-capture.null_ip' => '255.255.255.255']);

    expect(resolverFor()->getClientIp())->toBe('255.255.255.255');
});

it('falls back to the default null ip when the configured one is unusable', function () {
    config(['ip-capture.trust_proxies' => false, 'ip-capture.null_ip' => '']);

    expect(resolverFor()->getClientIp())->toBe('0.0.0.0');
});

it('returns the null ip without inspecting the request when disabled', function () {
    config(['ip-capture.enabled' => false]);

    expect(resolverFor(['REMOTE_ADDR' => '203.0.113.7'])->getClientIp())->toBe('0.0.0.0');
});

it('reads the cloudflare header before the other proxy headers', function () {
    config(['ip-capture.trust_proxies' => false]);

    $ip = resolverFor([
        'HTTP_CF_CONNECTING_IP' => '203.0.113.50',
        'HTTP_X_FORWARDED_FOR'  => '198.51.100.7',
        'REMOTE_ADDR'           => '127.0.0.1',
    ])->getClientIp();

    expect($ip)->toBe('203.0.113.50');
});

it('takes the client address from the front of a forwarded chain', function () {
    config(['ip-capture.trust_proxies' => false]);

    $ip = resolverFor([
        'HTTP_X_FORWARDED_FOR' => '203.0.113.50, 70.41.3.18, 150.172.238.178',
        'REMOTE_ADDR'          => '127.0.0.1',
    ])->getClientIp();

    expect($ip)->toBe('203.0.113.50');
});

it('skips a header holding something that is not an address', function () {
    config(['ip-capture.trust_proxies' => false]);

    $ip = resolverFor([
        'HTTP_CF_CONNECTING_IP' => 'unknown',
        'HTTP_X_FORWARDED_FOR'  => '198.51.100.7',
        'REMOTE_ADDR'           => '127.0.0.1',
    ])->getClientIp();

    expect($ip)->toBe('198.51.100.7');
});

it('only inspects the headers listed in config', function () {
    config([
        'ip-capture.trust_proxies' => false,
        'ip-capture.headers'       => ['REMOTE_ADDR'],
    ]);

    $ip = resolverFor([
        'HTTP_CF_CONNECTING_IP' => '203.0.113.50',
        'REMOTE_ADDR'           => '198.51.100.9',
    ])->getClientIp();

    expect($ip)->toBe('198.51.100.9');
});

it('resolves an ipv6 address', function () {
    expect(resolverFor(['REMOTE_ADDR' => '2001:db8:85a3::8a2e:370:7334'])->getClientIp())
        ->toBe('2001:db8:85a3::8a2e:370:7334');
});

it('hashes the address with the configured algorithm', function () {
    config(['ip-capture.hash' => true, 'ip-capture.hash_algo' => 'sha256']);

    expect(resolverFor(['REMOTE_ADDR' => '192.168.1.100'])->getClientIp())
        ->toBe(hash('sha256', '192.168.1.100'));
});

it('hashes with a custom algorithm', function () {
    config(['ip-capture.hash' => true, 'ip-capture.hash_algo' => 'md5']);

    expect(resolverFor(['REMOTE_ADDR' => '10.0.0.1'])->getClientIp())->toBe(hash('md5', '10.0.0.1'));
});

it('produces a different digest once a salt is set', function () {
    config(['ip-capture.hash' => true, 'ip-capture.hash_salt' => 'pepper']);

    $salted = resolverFor(['REMOTE_ADDR' => '10.0.0.1'])->getClientIp();

    expect($salted)->toBe(hash('sha256', 'pepper10.0.0.1'))
        ->and($salted)->not->toBe(hash('sha256', '10.0.0.1'));
});

it('rejects an unsupported hashing algorithm with a clear message', function () {
    config(['ip-capture.hash' => true, 'ip-capture.hash_algo' => 'not-a-real-algo']);

    expect(fn () => resolverFor(['REMOTE_ADDR' => '10.0.0.1'])->getClientIp())
        ->toThrow(InvalidArgumentException::class, 'Unsupported hashing algorithm [not-a-real-algo]');
});

it('anonymizes an ipv4 address down to its network', function () {
    config(['ip-capture.anonymize' => true]);

    expect(resolverFor(['REMOTE_ADDR' => '203.0.113.45'])->getClientIp())->toBe('203.0.113.0');
});

it('anonymizes an ipv6 address down to its network', function () {
    config(['ip-capture.anonymize' => true]);

    expect(resolverFor(['REMOTE_ADDR' => '2001:db8:85a3:1:1:8a2e:370:7334'])->getClientIp())
        ->toBe('2001:db8:85a3:1::');
});

it('anonymizes before hashing so the digest cannot be reversed to a host', function () {
    config(['ip-capture.anonymize' => true, 'ip-capture.hash' => true]);

    expect(resolverFor(['REMOTE_ADDR' => '203.0.113.45'])->getClientIp())
        ->toBe(hash('sha256', '203.0.113.0'));
});

it('prefers the address laravel resolved when proxies are trusted', function () {
    config(['ip-capture.trust_proxies' => true]);

    expect(resolverFor(['REMOTE_ADDR' => '192.168.1.50'])->getClientIp())->toBe('192.168.1.50');
});

it('falls through to the headers when laravel only sees localhost', function () {
    config(['ip-capture.trust_proxies' => true]);

    $ip = resolverFor([
        'REMOTE_ADDR'           => '127.0.0.1',
        'HTTP_CF_CONNECTING_IP' => '203.0.113.50',
    ])->getClientIp();

    expect($ip)->toBe('203.0.113.50');
});
