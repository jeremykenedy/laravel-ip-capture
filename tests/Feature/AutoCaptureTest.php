<?php

use Illuminate\Database\Eloquent\Model;
use Jeremykenedy\LaravelIpCapture\Tests\Fixtures\IpCaptureUser;
use Jeremykenedy\LaravelIpCapture\Tests\Fixtures\SoftDeletingIpCaptureUser;

beforeEach(function () {
    $this->createUsersTable();
    $this->artisan('migrate')->assertSuccessful();
    $this->withClientIp('203.0.113.42');
});

function enableAutoCapture(?array $events = null): void
{
    config(['ip-capture.auto_capture.enabled' => true]);

    if ($events !== null) {
        config(['ip-capture.auto_capture.events' => $events]);
    }

    Model::clearBootedModels();
}

it('writes nothing on its own with the shipped configuration', function () {
    $user = IpCaptureUser::create(['name' => 'no auto capture']);

    expect($user->fresh()->signup_ip_address)->toBeNull()
        ->and($user->fresh()->updated_ip_address)->toBeNull();
});

it('captures the signup address when creating with auto capture on', function () {
    enableAutoCapture();

    $user = IpCaptureUser::create(['name' => 'signing up']);

    expect($user->fresh()->signup_ip_address)->toBe('203.0.113.42');
});

it('captures the update address when saving an existing model', function () {
    enableAutoCapture();

    $user = IpCaptureUser::create(['name' => 'signing up']);
    $this->withClientIp('198.51.100.8');

    $user->update(['name' => 'changed later']);

    expect($user->fresh()->updated_ip_address)->toBe('198.51.100.8')
        ->and($user->fresh()->signup_ip_address)->toBe('203.0.113.42');
});

it('keeps a stored address when the current context has none to offer', function () {
    enableAutoCapture();

    $user = IpCaptureUser::create(['name' => 'signing up']);

    DB::table('users')->where('id', $user->getKey())->update(['updated_ip_address' => '198.51.100.8']);
    $user->refresh();

    $this->withClientIp(null);

    $user->update(['name' => 'changed from a queued job']);

    expect($user->fresh()->updated_ip_address)->toBe('198.51.100.8');
});

it('still records the null address on a first capture with no context', function () {
    enableAutoCapture();

    $this->withClientIp(null);

    $user = IpCaptureUser::create(['name' => 'created from the console']);

    expect($user->fresh()->signup_ip_address)->toBe('0.0.0.0');
});

it('skips an event mapped to a disabled column', function () {
    config(['ip-capture.columns.signup_ip_address' => false]);
    enableAutoCapture();

    $user = IpCaptureUser::create(['name' => 'signing up']);

    expect($user->fresh()->signup_ip_address)->toBeNull();
});

it('captures into whichever column the event is mapped to', function () {
    enableAutoCapture(['creating' => 'admin_ip_address']);

    $user = IpCaptureUser::create(['name' => 'created by an admin']);

    expect($user->fresh()->admin_ip_address)->toBe('203.0.113.42')
        ->and($user->fresh()->signup_ip_address)->toBeNull();
});

// Why the shipped events map leaves deleting out: the address is set on the
// model but a soft delete writes only its own columns.
it('does not persist an address captured while soft deleting', function () {
    enableAutoCapture(['deleting' => 'deleted_ip_address']);

    $user = SoftDeletingIpCaptureUser::create(['name' => 'going away']);
    $user->delete();

    $trashed = SoftDeletingIpCaptureUser::withTrashed()->find($user->getKey());

    expect($user->deleted_ip_address)->toBe('203.0.113.42')
        ->and($trashed->deleted_ip_address)->toBeNull();
});

it('records a deletion address when the model is saved before deleting', function () {
    $user = SoftDeletingIpCaptureUser::create(['name' => 'going away']);
    $user->setDeletedIp()->save();
    $user->delete();

    $trashed = SoftDeletingIpCaptureUser::withTrashed()->find($user->getKey());

    expect($trashed->deleted_ip_address)->toBe('203.0.113.42');
});
