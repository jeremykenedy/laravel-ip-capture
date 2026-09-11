<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Jeremykenedy\LaravelIpCapture\Contracts\IpResolverInterface;
use Jeremykenedy\LaravelIpCapture\Providers\IpCaptureServiceProvider;
use Jeremykenedy\LaravelIpCapture\Services\IpResolver;
use Jeremykenedy\LaravelIpCapture\Support\IpCapture;

it('registers the service provider', function () {
    expect(app()->getLoadedProviders())->toHaveKey(IpCaptureServiceProvider::class);
});

it('binds ip resolver interface to concrete class', function () {
    expect(app(IpResolverInterface::class))->toBeInstanceOf(IpResolver::class);
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
        'hash_salt',
        'anonymize',
        'headers',
        'auto_capture',
        'table',
        'column_length',
        'css_framework',
        'frontend',
        'views',
        'display',
    ]);
});

it('loads translations', function () {
    expect(trans('ip-capture::ip-capture.captured'))->toBe('IP address captured successfully.');
    expect(trans('ip-capture::ip-capture.label_ip'))->toBe('IP Address');
    expect(trans('ip-capture::ip-capture.label_date'))->toBe('Captured At');
    expect(trans('ip-capture::ip-capture.not_found'))->toBe('IP address not found.');
});

it('registers the view namespace for the active framework', function () {
    expect(View::exists('ip-capture::ip-table'))->toBeTrue()
        ->and(View::exists('ip-capture::ip-badge'))->toBeTrue();
});

it('registers a namespace for each shipped framework', function (string $framework) {
    expect(View::exists("ip-capture-{$framework}::ip-table"))->toBeTrue();
})->with([['tailwind'], ['bootstrap5'], ['bootstrap4']]);

it('falls back to the tailwind views for a framework with no own copy', function () {
    expect(IpCapture::viewPaths('bootstrap5'))->toBe([
        IpCapture::viewsPath().'/bootstrap5/blade',
        IpCapture::viewsPath().'/tailwind/blade',
    ]);
});

it('registers the blade components under the package namespace', function () {
    expect(Blade::render('<x-ip-capture::ip-badge ip="203.0.113.1" />'))->toContain('203.0.113.1');
});

it('leaves the view namespace unregistered when views are switched off', function () {
    config(['ip-capture.views.enabled' => false]);

    $provider = new IpCaptureServiceProvider($this->app);
    $provider->boot();

    expect(IpCapture::viewsEnabled())->toBeFalse();
});

it('registers a publish tag for every shipped asset', function (string $tag) {
    expect(ServiceProvider::pathsToPublish(IpCaptureServiceProvider::class, $tag))->not->toBeEmpty();
})->with([
    ['ip-capture-config'],
    ['ip-capture-migrations'],
    ['ip-capture-lang'],
    ['ip-capture-views'],
    ['ip-capture-views-tailwind'],
    ['ip-capture-views-bootstrap5'],
    ['ip-capture-views-bootstrap4'],
    ['ip-capture-js'],
    ['ip-capture-livewire'],
]);

it('publishes the config to the application config path', function () {
    $paths = ServiceProvider::pathsToPublish(IpCaptureServiceProvider::class, 'ip-capture-config');

    expect(array_values($paths))->toBe([config_path('ip-capture.php')]);
});

it('loads the bundled migration', function () {
    $paths = array_map(static fn (string $path): string => (string) realpath($path), app('migrator')->paths());

    expect($paths)->toContain(realpath(__DIR__.'/../../database/migrations'));
});

it('registers the three console commands', function () {
    expect(array_keys(app('Illuminate\Contracts\Console\Kernel')->all()))
        ->toContain('ip-capture:install', 'ip-capture:update', 'ip-capture:switch');
});
