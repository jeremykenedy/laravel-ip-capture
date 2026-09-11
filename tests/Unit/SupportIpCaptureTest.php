<?php

use Jeremykenedy\LaravelIpCapture\Support\IpCapture;

it('reports the enabled columns in configuration order', function () {
    config(['ip-capture.columns' => [
        'signup_ip_address'  => true,
        'admin_ip_address'   => false,
        'updated_ip_address' => true,
    ]]);

    expect(IpCapture::enabledColumns())->toBe(['signup_ip_address', 'updated_ip_address']);
});

it('treats anything other than true as a disabled column', function () {
    config(['ip-capture.columns' => ['signup_ip_address' => 1, 'admin_ip_address' => 'yes']]);

    expect(IpCapture::columnEnabled('signup_ip_address'))->toBeFalse()
        ->and(IpCapture::columnEnabled('admin_ip_address'))->toBeFalse();
});

it('falls back to the shipped headers when config has none', function () {
    config(['ip-capture.headers' => []]);

    expect(IpCapture::headers())->toBe(IpCapture::DEFAULT_HEADERS);
});

it('drops empty entries from a configured header list', function () {
    config(['ip-capture.headers' => ['HTTP_CF_CONNECTING_IP', '', 'REMOTE_ADDR']]);

    expect(IpCapture::headers())->toBe(['HTTP_CF_CONNECTING_IP', 'REMOTE_ADDR']);
});

it('falls back to tailwind for an unknown css framework', function () {
    config(['ip-capture.css_framework' => 'bulma']);

    expect(IpCapture::cssFramework())->toBe('tailwind');
});

it('falls back to blade for an unknown frontend', function () {
    config(['ip-capture.frontend' => 'angular']);

    expect(IpCapture::frontend())->toBe('blade');
});

it('inherits the framework from laravel-ui-kit when its own key is unset', function () {
    config(['ui-kit.css_framework' => 'bootstrap5', 'ui-kit.frontend' => 'livewire']);

    // The shipped config leaves the package keys unset, so this is what a
    // laravel-ui-kit application sees without touching ip-capture config.
    expect(config('ip-capture.css_framework'))->toBeNull()
        ->and(IpCapture::cssFramework())->toBe('bootstrap5')
        ->and(IpCapture::frontend())->toBe('livewire');
});

it('falls back to tailwind and blade with no ui-kit installed', function () {
    expect(IpCapture::cssFramework())->toBe('tailwind')
        ->and(IpCapture::frontend())->toBe('blade');
});

it('validates the shipped framework lists', function () {
    expect(IpCapture::isValidCssFramework('bootstrap4'))->toBeTrue()
        ->and(IpCapture::isValidCssFramework('foundation'))->toBeFalse()
        ->and(IpCapture::isValidFrontend('svelte'))->toBeTrue()
        ->and(IpCapture::isValidFrontend('ember'))->toBeFalse();
});

it('uses the translated label for a shipped column', function () {
    expect(IpCapture::columnLabel('signup_sm_ip_address'))->toBe('Social signup');
});

it('builds a readable label for a column it has no translation for', function () {
    expect(IpCapture::columnLabel('reset_ip_address'))->toBe('Reset ip address');
});

it('keeps a positive column length and rejects a nonsense one', function () {
    config(['ip-capture.column_length' => 128]);
    expect(IpCapture::columnLength())->toBe(128);

    config(['ip-capture.column_length' => 0]);
    expect(IpCapture::columnLength())->toBe(64);
});

it('anonymizes addresses to a /24 and a /64', function (string $input, string $expected) {
    expect(IpCapture::anonymize($input))->toBe($expected);
})->with([
    ['203.0.113.45', '203.0.113.0'],
    ['10.0.0.255', '10.0.0.0'],
    ['2001:db8:85a3:1:1:8a2e:370:7334', '2001:db8:85a3:1::'],
    ['not-an-address', 'not-an-address'],
]);

it('masks addresses for display', function (string $input, string $expected) {
    expect(IpCapture::mask($input))->toBe($expected);
})->with([
    ['203.0.113.45', '203.0.113.xxx'],
    ['2001:db8:85a3:1:1:8a2e:370:7334', '2001:db8:85a3:1::'],
    ['not-an-address', 'not-an-address'],
]);

it('reads the auto capture events as a map of event to column', function () {
    expect(IpCapture::autoCaptureEvents())->toBe([
        'creating' => 'signup_ip_address',
        'updating' => 'updated_ip_address',
    ]);
});

it('ignores an auto capture entry that names no column', function () {
    config(['ip-capture.auto_capture.events' => ['creating' => false, 'updating' => 'updated_ip_address']]);

    expect(IpCapture::autoCaptureEvents())->toBe(['updating' => 'updated_ip_address']);
});

it('applies anonymizing and hashing to any address it is given', function () {
    config(['ip-capture.anonymize' => true, 'ip-capture.hash' => true, 'ip-capture.hash_salt' => 'pepper']);

    expect(IpCapture::prepare('203.0.113.45'))->toBe(hash('sha256', 'pepper203.0.113.0'));
});

it('returns an address untouched when neither option is on', function () {
    expect(IpCapture::prepare('203.0.113.45'))->toBe('203.0.113.45');
});

it('rejects an unsupported algorithm when preparing an address', function () {
    config(['ip-capture.hash' => true, 'ip-capture.hash_algo' => 'not-a-real-algo']);

    expect(fn () => IpCapture::prepare('203.0.113.45'))
        ->toThrow(InvalidArgumentException::class, 'Unsupported hashing algorithm [not-a-real-algo]');
});

it('reports the null ip in the form it is actually stored in', function () {
    expect(IpCapture::preparedNullIp())->toBe('0.0.0.0');

    config(['ip-capture.hash' => true]);

    expect(IpCapture::preparedNullIp())->toBe(hash('sha256', '0.0.0.0'));
});

it('tells an absent columns key apart from an empty one', function () {
    expect(IpCapture::columnsConfigured())->toBeTrue();

    config(['ip-capture.columns' => []]);
    expect(IpCapture::columnsConfigured())->toBeTrue()
        ->and(IpCapture::enabledColumns())->toBe([]);
});

it('ignores an event that fires after the row is written', function () {
    config(['ip-capture.auto_capture.events' => [
        'creating' => 'signup_ip_address',
        'created'  => 'admin_ip_address',
        'saved'    => 'admin_ip_address',
        'updated'  => 'updated_ip_address',
        'deleted'  => 'deleted_ip_address',
    ]]);

    // Capture assigns an attribute, so only an event before the write can work.
    expect(IpCapture::autoCaptureEvents())->toBe(['creating' => 'signup_ip_address']);
});

it('accepts every event that fires before the row is written', function () {
    $events = array_combine(IpCapture::CAPTURABLE_EVENTS, array_fill(0, count(IpCapture::CAPTURABLE_EVENTS), 'signup_ip_address'));

    config(['ip-capture.auto_capture.events' => $events]);

    expect(IpCapture::autoCaptureEvents())->toBe($events);
});
