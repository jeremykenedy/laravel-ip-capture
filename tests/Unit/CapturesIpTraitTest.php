<?php

use Illuminate\Database\Eloquent\Model;
use Jeremykenedy\LaravelIpCapture\Traits\CapturesIp;

// Create a fake model for testing the trait
class FakeUserModel extends Model
{
    use CapturesIp;

    protected $guarded = [];

    public $signup_ip_address;
    public $signup_confirmation_ip_address;
    public $signup_sm_ip_address;
    public $admin_ip_address;
    public $updated_ip_address;
    public $deleted_ip_address;
}

it('captures ip returns a string', function () {
    $model = new FakeUserModel();
    $ip = $model->captureIp();
    expect($ip)->toBeString();
});

it('sets signup ip', function () {
    $model = new FakeUserModel();
    $model->setSignupIp();
    expect($model->signup_ip_address)->toBeString();
});

it('sets confirmation ip', function () {
    $model = new FakeUserModel();
    $model->setSignupConfirmationIp();
    expect($model->signup_confirmation_ip_address)->toBeString();
});

it('sets social signup ip', function () {
    $model = new FakeUserModel();
    $model->setSocialSignupIp();
    expect($model->signup_sm_ip_address)->toBeString();
});

it('sets admin ip', function () {
    $model = new FakeUserModel();
    $model->setAdminIp();
    expect($model->admin_ip_address)->toBeString();
});

it('sets updated ip', function () {
    $model = new FakeUserModel();
    $model->setUpdatedIp();
    expect($model->updated_ip_address)->toBeString();
});

it('sets deleted ip', function () {
    $model = new FakeUserModel();
    $model->setDeletedIp();
    expect($model->deleted_ip_address)->toBeString();
});

it('skips disabled columns', function () {
    config(['ip-capture.columns.signup_ip_address' => false]);
    $model = new FakeUserModel();
    $model->setSignupIp();
    expect($model->signup_ip_address)->toBeNull();
});

it('sets arbitrary ip column', function () {
    config(['ip-capture.columns.custom_ip' => true]);
    $model = new FakeUserModel();
    $model->custom_ip = null;
    $model->setIpColumn('custom_ip');
    expect($model->custom_ip)->toBeString();
});

it('returns all ip columns', function () {
    $model = new FakeUserModel();
    $model->setSignupIp();
    $model->setUpdatedIp();

    $columns = $model->getIpColumns();
    expect($columns)->toBeArray();
    expect($columns)->toHaveKey('signup_ip_address');
    expect($columns)->toHaveKey('updated_ip_address');
});

it('returns fluent interface', function () {
    $model = new FakeUserModel();
    $result = $model->setSignupIp()->setUpdatedIp()->setDeletedIp();
    expect($result)->toBeInstanceOf(FakeUserModel::class);
});
