<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    File::put(config_path('ip-capture.php'), '<?php return [];');
});

it('updates every css and frontend combination', function (string $css, string $frontend) {
    $this->artisan('ip-capture:update', ['--css' => $css, '--frontend' => $frontend])->assertSuccessful();

    expect(config('ip-capture.css_framework'))->toBe($css)
        ->and(config('ip-capture.frontend'))->toBe($frontend);
})->with('frameworks');

it('changes only the css framework when that is all it is given', function () {
    config(['ip-capture.frontend' => 'livewire']);

    $this->artisan('ip-capture:update', ['--css' => 'bootstrap4'])->assertSuccessful();

    expect(config('ip-capture.css_framework'))->toBe('bootstrap4')
        ->and(config('ip-capture.frontend'))->toBe('livewire');
});

it('changes only the frontend when that is all it is given', function () {
    config(['ip-capture.css_framework' => 'bootstrap5']);

    $this->artisan('ip-capture:update', ['--frontend' => 'react'])->assertSuccessful();

    expect(config('ip-capture.frontend'))->toBe('react')
        ->and(config('ip-capture.css_framework'))->toBe('bootstrap5');
});

it('publishes the javascript components when moving to a javascript frontend', function () {
    $this->artisan('ip-capture:update', ['--frontend' => 'vue'])->assertSuccessful();

    expect(File::exists(resource_path('js/vendor/ip-capture/vue/IpTable.vue')))->toBeTrue();
});

it('tells the reader how to publish the views for the new framework', function () {
    $this->artisan('ip-capture:update', ['--css' => 'bootstrap5'])
        ->expectsOutputToContain('vendor:publish --tag=ip-capture-views --force')
        ->assertSuccessful();
});

it('rejects an unknown css framework', function () {
    $this->artisan('ip-capture:update', ['--css' => 'bulma'])
        ->expectsOutputToContain('Invalid CSS framework: bulma')
        ->assertFailed();
});

it('rejects an unknown frontend', function () {
    $this->artisan('ip-capture:update', ['--frontend' => 'ember'])
        ->expectsOutputToContain('Invalid frontend: ember')
        ->assertFailed();
});

it('leaves the configuration alone when a flag is invalid', function () {
    config(['ip-capture.css_framework' => 'tailwind']);

    $this->artisan('ip-capture:update', ['--css' => 'bulma'])->assertFailed();

    expect(config('ip-capture.css_framework'))->toBe('tailwind');
});

it('points at the install command when the package is not installed', function () {
    File::delete(config_path('ip-capture.php'));

    $this->artisan('ip-capture:update', ['--css' => 'bootstrap5'])
        ->expectsOutputToContain('IP Capture is not installed yet.')
        ->expectsOutputToContain('php artisan ip-capture:install')
        ->assertFailed();
});

it('keeps the configuration alone when it is not installed', function () {
    File::delete(config_path('ip-capture.php'));
    config(['ip-capture.css_framework' => 'tailwind']);

    $this->artisan('ip-capture:update', ['--css' => 'bootstrap5'])->assertFailed();

    expect(config('ip-capture.css_framework'))->toBe('tailwind');
});

it('uses the configured frameworks when run without flags or prompts', function () {
    config(['ip-capture.css_framework' => 'bootstrap4', 'ip-capture.frontend' => 'svelte']);

    $this->artisan('ip-capture:update', ['--no-interaction' => true])->assertSuccessful();

    expect(config('ip-capture.css_framework'))->toBe('bootstrap4')
        ->and(config('ip-capture.frontend'))->toBe('svelte');
});

it('leaves a published livewire component as the application edited it', function () {
    $this->artisan('ip-capture:update', ['--frontend' => 'livewire'])->assertSuccessful();

    File::put(app_path('Livewire/IpTable.php'), '<?php // edited by the application');

    $this->artisan('ip-capture:update', ['--frontend' => 'livewire'])->assertSuccessful();

    expect(File::get(app_path('Livewire/IpTable.php')))->toBe('<?php // edited by the application');
});

it('leaves a published javascript component as the application edited it', function () {
    $this->artisan('ip-capture:update', ['--frontend' => 'vue'])->assertSuccessful();

    File::put(resource_path('js/vendor/ip-capture/vue/IpTable.vue'), '// edited by the application');

    $this->artisan('ip-capture:update', ['--frontend' => 'vue'])->assertSuccessful();

    expect(File::get(resource_path('js/vendor/ip-capture/vue/IpTable.vue')))->toBe('// edited by the application');
});
