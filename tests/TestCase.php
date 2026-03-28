<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Tests;

use Jeremykenedy\LaravelIpCapture\Providers\IpCaptureServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            IpCaptureServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
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
}
