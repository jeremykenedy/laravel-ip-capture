<?php

use Illuminate\Support\ServiceProvider;
use Jeremykenedy\LaravelIpCapture\Contracts\IpResolverInterface;
use Jeremykenedy\LaravelIpCapture\Providers\IpCaptureServiceProvider;
use Jeremykenedy\LaravelIpCapture\Services\IpResolver;
use Jeremykenedy\LaravelIpCapture\Traits\CapturesIp;

/**
 * The surface consuming applications already depend on. Changing anything
 * asserted here is a breaking change and needs a major version.
 */
it('keeps the published class and trait names', function (string $name) {
    expect(class_exists($name) || interface_exists($name) || trait_exists($name))->toBeTrue();
})->with([
    ['Jeremykenedy\LaravelIpCapture\Traits\CapturesIp'],
    ['Jeremykenedy\LaravelIpCapture\Contracts\IpResolverInterface'],
    ['Jeremykenedy\LaravelIpCapture\Services\IpResolver'],
    ['Jeremykenedy\LaravelIpCapture\Providers\IpCaptureServiceProvider'],
]);

it('keeps every trait method callable with the same required arguments', function (string $method, int $required) {
    $reflection = new ReflectionMethod(CapturesIp::class, $method);

    expect($reflection->isPublic())->toBeTrue()
        ->and($reflection->getNumberOfRequiredParameters())->toBe($required);
})->with([
    ['captureIp', 0],
    ['setSignupIp', 0],
    ['setSignupConfirmationIp', 0],
    ['setSocialSignupIp', 0],
    ['setAdminIp', 0],
    ['setUpdatedIp', 0],
    ['setDeletedIp', 0],
    ['setIpColumn', 1],
    ['getIpColumns', 0],
]);

it('keeps the fluent setters returning the model', function (string $method) {
    expect((string) (new ReflectionMethod(CapturesIp::class, $method))->getReturnType())->toBe('static');
})->with([
    ['setSignupIp'],
    ['setSignupConfirmationIp'],
    ['setSocialSignupIp'],
    ['setAdminIp'],
    ['setUpdatedIp'],
    ['setDeletedIp'],
    ['setIpColumn'],
]);

it('keeps the resolver contract at a single string returning method', function () {
    $reflection = new ReflectionClass(IpResolverInterface::class);

    expect(array_map(fn ($method) => $method->getName(), $reflection->getMethods()))->toBe(['getClientIp'])
        ->and((string) $reflection->getMethod('getClientIp')->getReturnType())->toBe('string');
});

it('keeps the resolver constructible from a request alone', function () {
    $constructor = (new ReflectionClass(IpResolver::class))->getConstructor();

    expect($constructor?->getNumberOfRequiredParameters())->toBe(1)
        ->and((string) $constructor?->getParameters()[0]->getType())->toBe('Illuminate\Http\Request');
});

it('keeps the container binding for the resolver contract', function () {
    expect(app(IpResolverInterface::class))->toBeInstanceOf(IpResolver::class);
});

it('keeps the provider registered under its published name', function () {
    expect(app()->getLoadedProviders())->toHaveKey(IpCaptureServiceProvider::class);
});

it('keeps the config keys shipped since 1.0', function (string $key) {
    // has() rather than a value check, because hash ships as false.
    expect(config()->has("ip-capture.{$key}"))->toBeTrue();
})->with([
    ['enabled'],
    ['null_ip'],
    ['columns'],
    ['trust_proxies'],
    ['hash'],
    ['hash_algo'],
]);

it('keeps the six shipped column names', function () {
    expect(array_keys(config('ip-capture.columns')))->toBe([
        'signup_ip_address',
        'signup_confirmation_ip_address',
        'signup_sm_ip_address',
        'admin_ip_address',
        'updated_ip_address',
        'deleted_ip_address',
    ]);
});

it('keeps the publish tags shipped since 1.0', function (string $tag) {
    expect(ServiceProvider::pathsToPublish(IpCaptureServiceProvider::class, $tag))->not->toBeEmpty();
})->with([
    ['ip-capture-config'],
    ['ip-capture-migrations'],
    ['ip-capture-lang'],
]);

it('keeps the translation keys shipped since 1.0', function () {
    expect(trans('ip-capture::ip-capture.captured'))->toBe('IP address captured successfully.')
        ->and(trans('ip-capture::ip-capture.label_ip'))->toBe('IP Address')
        ->and(trans('ip-capture::ip-capture.label_date'))->toBe('Captured At')
        ->and(trans('ip-capture::ip-capture.not_found'))->toBe('IP address not found.');
});

it('ships exactly one migration, named for when it was written', function () {
    $migrations = glob(__DIR__.'/../../database/migrations/*.php');

    expect($migrations)->toHaveCount(1)
        ->and(basename($migrations[0]))->toBe('2026_03_28_135324_add_ip_capture_columns_to_users_table.php');
});

it('still recognises the file name the migration shipped under before 1.2', function () {
    $migration = (string) file_get_contents(
        __DIR__.'/../../database/migrations/2026_03_28_135324_add_ip_capture_columns_to_users_table.php'
    );

    expect($migration)->toContain('2025_01_01_000000_add_ip_capture_columns_to_users_table');
});
