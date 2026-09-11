<?php

use Illuminate\Support\Facades\Blade;
use Jeremykenedy\LaravelIpCapture\Support\IpCapture;
use Jeremykenedy\LaravelIpCapture\Tests\Fixtures\IpCaptureUser;

beforeEach(function () {
    $this->createUsersTable();
});

it('renders a row for every enabled column', function () {
    $html = Blade::render('<x-ip-capture::ip-table :columns="$columns" />', [
        'columns' => [
            'signup_ip_address'  => '203.0.113.10',
            'updated_ip_address' => null,
        ],
    ]);

    expect($html)
        ->toContain('data-column="signup_ip_address"')
        ->toContain('203.0.113.10')
        ->toContain('data-column="updated_ip_address"')
        ->toContain('Signup')
        ->toContain('Last update');
});

it('shows the empty label for a column that holds nothing', function () {
    config(['ip-capture.display.empty_label' => 'not captured']);

    $html = Blade::render('<x-ip-capture::ip-table :columns="[\'signup_ip_address\' => null]" />');

    expect($html)->toContain('not captured');
});

it('masks the host part when masking is switched on', function () {
    config(['ip-capture.display.mask' => true]);

    $html = Blade::render('<x-ip-capture::ip-table :columns="[\'signup_ip_address\' => \'203.0.113.45\']" />');

    expect($html)->toContain('203.0.113.xxx')->not->toContain('203.0.113.45');
});

it('lets the caller override masking per component', function () {
    config(['ip-capture.display.mask' => true]);

    $html = Blade::render('<x-ip-capture::ip-table :columns="[\'signup_ip_address\' => \'203.0.113.45\']" :mask="false" />');

    expect($html)->toContain('203.0.113.45');
});

it('reads the columns off a model when no columns are given', function () {
    config(['ip-capture.columns' => ['signup_ip_address' => true]]);

    $this->artisan('migrate')->assertSuccessful();

    $user = new IpCaptureUser();
    $user->signup_ip_address = '198.51.100.22';

    $html = Blade::render('<x-ip-capture::ip-table :model="$model" />', ['model' => $user]);

    expect($html)->toContain('198.51.100.22');
});

it('uses the given title over the translated one', function () {
    $html = Blade::render('<x-ip-capture::ip-table title="Audit trail" :columns="[]" />');

    expect($html)->toContain('Audit trail')->not->toContain('Captured IP addresses');
});

it('says so when there is nothing to show', function () {
    $html = Blade::render('<x-ip-capture::ip-table :columns="[]" />');

    expect($html)->toContain('No IP addresses have been captured yet.');
});

it('only renders the refresh control when asked to be live', function () {
    $plain = Blade::render('<x-ip-capture::ip-table :columns="[]" />');
    $live = Blade::render('<x-ip-capture::ip-table :columns="[]" :live="true" />');

    expect($plain)->not->toContain('wire:click')
        ->and($live)->toContain('wire:click="$refresh"');
});

it('renders the markup of the active css framework', function (string $framework, string $needle) {
    $this->useCssFramework($framework);

    $html = Blade::render('<x-ip-capture::ip-table :columns="[\'signup_ip_address\' => \'203.0.113.10\']" />');

    expect($html)->toContain($needle);
})->with([
    ['tailwind', 'rounded-xl border border-gray-200'],
    ['bootstrap5', 'fw-normal text-muted'],
    ['bootstrap4', 'font-weight-normal'],
]);

it('renders a badge for a single address', function () {
    $html = Blade::render('<x-ip-capture::ip-badge ip="203.0.113.10" label="Signup" />');

    expect($html)->toContain('203.0.113.10')->toContain('Signup');
});

it('renders a badge in the empty state when it has no address', function () {
    $html = Blade::render('<x-ip-capture::ip-badge />');

    expect($html)->toContain(IpCapture::emptyLabel());
});

it('passes extra attributes through to the rendered element', function () {
    $html = Blade::render('<x-ip-capture::ip-table id="audit" :columns="[]" />');

    expect($html)->toContain('id="audit"');
});

it('renders the new framework when the framework changes twice over', function () {
    $this->useCssFramework('bootstrap5');
    $first = Blade::render('<x-ip-capture::ip-table :columns="[\'signup_ip_address\' => \'203.0.113.10\']" />');

    $this->useCssFramework('bootstrap4');
    $second = Blade::render('<x-ip-capture::ip-table :columns="[\'signup_ip_address\' => \'203.0.113.10\']" />');

    expect($first)->toContain('fw-normal text-muted')->not->toContain('font-weight-normal')
        ->and($second)->toContain('font-weight-normal')->not->toContain('fw-normal');
});
