<?php

use Illuminate\Support\Facades\File;

it('switches every css and frontend combination', function (string $css, string $frontend) {
    $this->artisan('ip-capture:switch', ['--css' => $css, '--frontend' => $frontend])->assertSuccessful();

    expect(config('ip-capture.css_framework'))->toBe($css)
        ->and(config('ip-capture.frontend'))->toBe($frontend);
})->with('frameworks');

it('switches the css framework on its own', function () {
    config(['ip-capture.frontend' => 'react']);

    $this->artisan('ip-capture:switch', ['--css' => 'bootstrap5'])
        ->expectsOutputToContain('IP Capture CSS framework switched to: bootstrap5')
        ->assertSuccessful();

    expect(config('ip-capture.css_framework'))->toBe('bootstrap5')
        ->and(config('ip-capture.frontend'))->toBe('react');
});

it('switches the frontend on its own', function () {
    config(['ip-capture.css_framework' => 'bootstrap4']);

    $this->artisan('ip-capture:switch', ['--frontend' => 'livewire'])
        ->expectsOutputToContain('IP Capture frontend switched to: livewire')
        ->assertSuccessful();

    expect(config('ip-capture.frontend'))->toBe('livewire')
        ->and(config('ip-capture.css_framework'))->toBe('bootstrap4');
});

it('asks for at least one flag and shows how to use it', function () {
    $this->artisan('ip-capture:switch')
        ->expectsOutputToContain('Provide at least one of --css or --frontend.')
        ->expectsOutputToContain('php artisan ip-capture:switch --css=bootstrap5')
        ->assertFailed();
});

it('rejects an unknown css framework', function () {
    $this->artisan('ip-capture:switch', ['--css' => 'foundation'])
        ->expectsOutputToContain('Invalid CSS framework: foundation')
        ->assertFailed();
});

it('rejects an unknown frontend', function () {
    $this->artisan('ip-capture:switch', ['--frontend' => 'jquery'])
        ->expectsOutputToContain('Invalid frontend: jquery')
        ->assertFailed();
});

it('changes nothing when one of two flags is invalid', function () {
    config(['ip-capture.css_framework' => 'tailwind', 'ip-capture.frontend' => 'blade']);

    $this->artisan('ip-capture:switch', ['--css' => 'bootstrap5', '--frontend' => 'jquery'])->assertFailed();

    expect(config('ip-capture.css_framework'))->toBe('tailwind')
        ->and(config('ip-capture.frontend'))->toBe('blade');
});

it('works without the package having been installed first', function () {
    File::delete(config_path('ip-capture.php'));

    $this->artisan('ip-capture:switch', ['--css' => 'bootstrap5'])->assertSuccessful();
});

it('reminds the reader to clear the compiled views', function () {
    $this->artisan('ip-capture:switch', ['--css' => 'bootstrap5'])
        ->expectsOutputToContain('php artisan view:clear')
        ->assertSuccessful();
});

it('names the publish command when switching to a javascript frontend', function () {
    $this->artisan('ip-capture:switch', ['--frontend' => 'vue'])
        ->expectsOutputToContain('vendor:publish --tag=ip-capture-js')
        ->assertSuccessful();
});

it('names the publish command when switching to livewire', function () {
    $this->artisan('ip-capture:switch', ['--frontend' => 'livewire'])
        ->expectsOutputToContain('vendor:publish --tag=ip-capture-livewire')
        ->assertSuccessful();
});

it('tells the reader to republish views when switching css framework', function () {
    $this->artisan('ip-capture:switch', ['--css' => 'bootstrap5'])
        ->expectsOutputToContain('vendor:publish --tag=ip-capture-views-bootstrap5 --force')
        ->assertSuccessful();
});

it('names no publish command when switching to blade', function () {
    $this->artisan('ip-capture:switch', ['--frontend' => 'blade'])
        ->doesntExpectOutputToContain('ip-capture-js')
        ->assertSuccessful();
});

it('reports a zero as the invalid value it is', function () {
    $this->artisan('ip-capture:switch', ['--css' => '0', '--frontend' => 'vue'])
        ->expectsOutputToContain('Invalid CSS framework: 0')
        ->assertFailed();

    expect(config('ip-capture.frontend'))->not->toBe('vue');
});
