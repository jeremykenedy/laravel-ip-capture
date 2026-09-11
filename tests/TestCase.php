<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Jeremykenedy\LaravelIpCapture\Providers\IpCaptureServiceProvider;
use Jeremykenedy\LaravelIpCapture\Support\IpCapture;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->removePublishedFiles();

        Model::clearBootedModels();
    }

    protected function tearDown(): void
    {
        $this->removePublishedFiles();

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            IpCaptureServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('ip-capture.enabled', true);
        $app['config']->set('ip-capture.null_ip', '0.0.0.0');
        $app['config']->set('ip-capture.hash', false);
        $app['config']->set('ip-capture.columns', [
            'signup_ip_address'              => true,
            'signup_confirmation_ip_address' => true,
            'signup_sm_ip_address'           => true,
            'admin_ip_address'               => true,
            'updated_ip_address'             => true,
            'deleted_ip_address'             => true,
        ]);
    }

    /**
     * Build the table the package migration expects to find.
     */
    protected function createUsersTable(string $table = 'users'): void
    {
        Schema::create($table, function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name')->nullable();
            $blueprint->string('email')->nullable();
            $blueprint->string('password')->nullable();
            $blueprint->timestamps();
            $blueprint->softDeletes();
        });
    }

    /**
     * Send a request through the container so the resolver sees a known address.
     */
    protected function withClientIp(?string $ip, array $server = []): void
    {
        $request = Request::create('/', 'GET', [], [], [], array_merge(
            $ip === null ? [] : ['REMOTE_ADDR' => $ip],
            $server,
        ));

        // Request::create always supplies a localhost REMOTE_ADDR, which hides
        // the case where nothing at all is known about the client.
        if ($ip === null && !array_key_exists('REMOTE_ADDR', $server)) {
            $request->server->remove('REMOTE_ADDR');
        }

        $this->app->instance('request', $request);
    }

    /**
     * Point the view namespace at one framework, the way a fresh boot would.
     */
    protected function useCssFramework(string $framework): void
    {
        config(['ip-capture.css_framework' => $framework]);

        View::replaceNamespace(IpCapture::VIEW_NAMESPACE, IpCapture::viewPaths($framework));
    }

    /**
     * The install command writes real files into the test skeleton, so every
     * test starts and ends without them.
     */
    protected function removePublishedFiles(): void
    {
        File::delete(config_path('ip-capture.php'));
        File::delete(app_path('Livewire/IpTable.php'));
        File::delete(resource_path('views/livewire/ip-table.blade.php'));
        File::deleteDirectory(resource_path('views/vendor/ip-capture'));
        File::deleteDirectory(resource_path('js/vendor/ip-capture'));
    }
}
