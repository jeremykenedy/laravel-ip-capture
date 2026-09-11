<?php

use Jeremykenedy\LaravelIpCapture\Tests\Fixtures\IpCaptureUser;

beforeEach(function () {
    $this->withClientIp('203.0.113.42');
});

it('captures the address the resolver reports', function () {
    expect((new IpCaptureUser())->captureIp())->toBe('203.0.113.42');
});

it('sets each shipped column to the captured address', function (string $method, string $column) {
    $model = new IpCaptureUser();
    $model->{$method}();

    expect($model->{$column})->toBe('203.0.113.42');
})->with([
    ['setSignupIp', 'signup_ip_address'],
    ['setSignupConfirmationIp', 'signup_confirmation_ip_address'],
    ['setSocialSignupIp', 'signup_sm_ip_address'],
    ['setAdminIp', 'admin_ip_address'],
    ['setUpdatedIp', 'updated_ip_address'],
    ['setDeletedIp', 'deleted_ip_address'],
]);

it('leaves a disabled column untouched', function () {
    config(['ip-capture.columns.signup_ip_address' => false]);

    $model = new IpCaptureUser();
    $model->setSignupIp();

    expect($model->signup_ip_address)->toBeNull();
});

it('sets a column that was added to config', function () {
    config(['ip-capture.columns.custom_ip' => true]);

    $model = new IpCaptureUser();
    $model->setIpColumn('custom_ip');

    expect($model->custom_ip)->toBe('203.0.113.42');
});

it('ignores a column that is not in config', function () {
    $model = new IpCaptureUser();
    $model->setIpColumn('column_nobody_configured');

    expect($model->column_nobody_configured)->toBeNull();
});

it('accepts an explicit address instead of resolving one', function () {
    $model = new IpCaptureUser();
    $model->setIpColumn('signup_ip_address', '198.51.100.4');

    expect($model->signup_ip_address)->toBe('198.51.100.4');
});

it('returns only the columns that hold a value', function () {
    $model = new IpCaptureUser();
    $model->setSignupIp();
    $model->setUpdatedIp();

    expect($model->getIpColumns())->toBe([
        'signup_ip_address'  => '203.0.113.42',
        'updated_ip_address' => '203.0.113.42',
    ]);
});

it('omits disabled columns from the column list', function () {
    config(['ip-capture.columns.signup_ip_address' => false]);

    $model = new IpCaptureUser();
    $model->signup_ip_address = '198.51.100.1';
    $model->setUpdatedIp();

    expect($model->getIpColumns())->toBe(['updated_ip_address' => '203.0.113.42']);
});

it('stores the hashed value when hashing is on', function () {
    config(['ip-capture.hash' => true]);

    $model = new IpCaptureUser();
    $model->setSignupIp();

    expect($model->signup_ip_address)->toBe(hash('sha256', '203.0.113.42'));
});

it('chains the setters', function () {
    $model = new IpCaptureUser();

    $result = $model->setSignupIp()->setUpdatedIp()->setDeletedIp();

    expect($result)->toBe($model)
        ->and($model->deleted_ip_address)->toBe('203.0.113.42');
});

it('hashes an address supplied by the caller, rather than storing it raw', function () {
    config(['ip-capture.hash' => true]);

    $model = new IpCaptureUser();
    $model->setIpColumn('signup_ip_address', '198.51.100.4');

    expect($model->signup_ip_address)->toBe(hash('sha256', '198.51.100.4'))
        ->and($model->signup_ip_address)->not->toBe('198.51.100.4');
});

it('anonymizes an address supplied by the caller', function () {
    config(['ip-capture.anonymize' => true]);

    $model = new IpCaptureUser();
    $model->setIpColumn('signup_ip_address', '198.51.100.4');

    expect($model->signup_ip_address)->toBe('198.51.100.0');
});

it('writes no column at all when capture is switched off', function () {
    config(['ip-capture.enabled' => false]);

    $model = new IpCaptureUser();
    $model->setSignupIp()->setUpdatedIp()->setIpColumn('admin_ip_address', '198.51.100.4');

    expect($model->signup_ip_address)->toBeNull()
        ->and($model->updated_ip_address)->toBeNull()
        ->and($model->admin_ip_address)->toBeNull()
        ->and($model->getIpColumns())->toBe([]);
});

it('still reads stored columns while capture is switched off', function () {
    $model = new IpCaptureUser();
    $model->setSignupIp();

    config(['ip-capture.enabled' => false]);

    expect($model->getIpColumns())->toBe(['signup_ip_address' => '203.0.113.42']);
});
