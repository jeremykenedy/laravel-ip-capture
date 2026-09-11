<?php

use Illuminate\Support\Facades\File;
use Jeremykenedy\LaravelIpCapture\Support\IpCapture;

it('installs every css and frontend combination', function (string $css, string $frontend) {
    $this->artisan('ip-capture:install', [
        '--css'      => $css,
        '--frontend' => $frontend,
        '--force'    => true,
    ])->assertSuccessful();

    expect(config('ip-capture.css_framework'))->toBe($css)
        ->and(config('ip-capture.frontend'))->toBe($frontend)
        ->and(File::exists(config_path('ip-capture.php')))->toBeTrue();
})->with('frameworks');

it('publishes the views of the framework it just selected', function (string $css, string $needle) {
    $this->artisan('ip-capture:install', [
        '--css'      => $css,
        '--frontend' => 'blade',
        '--force'    => true,
    ])->assertSuccessful();

    $published = resource_path('views/vendor/ip-capture/ip-table.blade.php');

    expect(File::exists($published))->toBeTrue()
        ->and(File::get($published))->toContain($needle);
})->with([
    ['tailwind', 'rounded-xl'],
    ['bootstrap5', 'fw-normal text-muted'],
    ['bootstrap4', 'font-weight-normal'],
]);

it('publishes the javascript components for a javascript frontend', function (string $frontend, string $file) {
    $this->artisan('ip-capture:install', [
        '--css'      => 'tailwind',
        '--frontend' => $frontend,
        '--force'    => true,
    ])->assertSuccessful();

    expect(File::exists(resource_path('js/vendor/ip-capture/'.$file)))->toBeTrue();
})->with([
    ['vue', 'vue/IpTable.vue'],
    ['react', 'react/IpTable.jsx'],
    ['svelte', 'svelte/IpTable.svelte'],
]);

it('publishes the livewire component for the livewire frontend', function () {
    $this->artisan('ip-capture:install', [
        '--css'      => 'tailwind',
        '--frontend' => 'livewire',
        '--force'    => true,
    ])->assertSuccessful();

    expect(File::exists(app_path('Livewire/IpTable.php')))->toBeTrue()
        ->and(File::exists(resource_path('views/livewire/ip-table.blade.php')))->toBeTrue();
});

it('leaves the javascript components unpublished for a blade install', function () {
    $this->artisan('ip-capture:install', [
        '--css'      => 'tailwind',
        '--frontend' => 'blade',
        '--force'    => true,
    ])->assertSuccessful();

    expect(File::exists(resource_path('js/vendor/ip-capture/vue/IpTable.vue')))->toBeFalse()
        ->and(File::exists(app_path('Livewire/IpTable.php')))->toBeFalse();
});

it('rejects an unknown css framework', function () {
    $this->artisan('ip-capture:install', ['--css' => 'bulma', '--frontend' => 'blade', '--force' => true])
        ->expectsOutputToContain('Invalid CSS framework: bulma')
        ->assertFailed();
});

it('rejects an unknown frontend', function () {
    $this->artisan('ip-capture:install', ['--css' => 'tailwind', '--frontend' => 'angular', '--force' => true])
        ->expectsOutputToContain('Invalid frontend: angular')
        ->assertFailed();
});

it('refuses to reinstall without confirmation when running non interactively', function () {
    File::put(config_path('ip-capture.php'), '<?php return [];');

    $this->artisan('ip-capture:install', ['--no-interaction' => true])
        ->expectsOutputToContain('Already installed. Use --force to reinstall non-interactively.')
        ->assertFailed();
});

it('reinstalls over an existing install when forced', function () {
    File::put(config_path('ip-capture.php'), '<?php return [];');

    $this->artisan('ip-capture:install', [
        '--css'      => 'bootstrap5',
        '--frontend' => 'blade',
        '--force'    => true,
    ])->assertSuccessful();

    expect(File::get(config_path('ip-capture.php')))->toContain('IP Capture Enabled');
});

it('cancels a reinstall when the confirmation is not the word yes', function () {
    File::put(config_path('ip-capture.php'), '<?php return [];');

    $this->artisan('ip-capture:install', ['--css' => 'bootstrap5', '--frontend' => 'blade'])
        ->expectsQuestion('  Type "yes" to reinstall from scratch, or any other key to cancel', 'no')
        ->expectsOutputToContain('Cancelled. No changes were made.')
        ->assertSuccessful();

    expect(File::get(config_path('ip-capture.php')))->toBe('<?php return [];');
});

it('continues a reinstall when the confirmation is the word yes', function () {
    File::put(config_path('ip-capture.php'), '<?php return [];');

    $this->artisan('ip-capture:install', ['--css' => 'bootstrap5', '--frontend' => 'blade'])
        ->expectsQuestion('  Type "yes" to reinstall from scratch, or any other key to cancel', 'yes')
        ->assertSuccessful();

    expect(config('ip-capture.css_framework'))->toBe('bootstrap5');
});

it('falls back to the configured frameworks when run without flags or prompts', function () {
    config(['ip-capture.css_framework' => 'bootstrap4', 'ip-capture.frontend' => 'vue']);

    $this->artisan('ip-capture:install', ['--no-interaction' => true, '--force' => true])->assertSuccessful();

    expect(config('ip-capture.css_framework'))->toBe('bootstrap4')
        ->and(config('ip-capture.frontend'))->toBe('vue');
});

it('names the package and the next steps in its summary', function () {
    $this->artisan('ip-capture:install', ['--css' => 'tailwind', '--frontend' => 'blade', '--force' => true])
        ->expectsOutputToContain('Laravel IP Capture installed successfully.')
        ->expectsOutputToContain('php artisan migrate')
        ->assertSuccessful();
});

it('registers all three commands', function () {
    expect(array_keys(app('Illuminate\Contracts\Console\Kernel')->all()))
        ->toContain('ip-capture:install', 'ip-capture:update', 'ip-capture:switch');
});

it('keeps the framework lists it advertises in its signature', function () {
    expect(IpCapture::CSS_FRAMEWORKS)->toBe(['tailwind', 'bootstrap5', 'bootstrap4'])
        ->and(IpCapture::FRONTENDS)->toBe(['blade', 'livewire', 'vue', 'react', 'svelte']);
});

it('rejects an invalid css framework given without a frontend and no prompts', function () {
    $this->artisan('ip-capture:install', [
        '--css'            => 'bulma',
        '--no-interaction' => true,
        '--force'          => true,
    ])->expectsOutputToContain('Invalid CSS framework: bulma')->assertFailed();

    expect(config('ip-capture.css_framework'))->not->toBe('bulma');
});

it('rejects an invalid frontend given without a css framework and no prompts', function () {
    $this->artisan('ip-capture:install', [
        '--frontend'       => 'ember',
        '--no-interaction' => true,
        '--force'          => true,
    ])->expectsOutputToContain('Invalid frontend: ember')->assertFailed();

    expect(config('ip-capture.frontend'))->not->toBe('ember');
});
