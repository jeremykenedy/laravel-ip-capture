<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Providers;

use Illuminate\Support\ServiceProvider;
use Jeremykenedy\LaravelIpCapture\Contracts\IpResolverInterface;
use Jeremykenedy\LaravelIpCapture\Services\IpResolver;

class IpCaptureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/ip-capture.php', 'ip-capture');

        $this->app->bind(IpResolverInterface::class, IpResolver::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'ip-capture');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/ip-capture.php' => config_path('ip-capture.php'),
            ], 'ip-capture-config');

            $this->publishes([
                __DIR__.'/../../database/migrations/' => database_path('migrations'),
            ], 'ip-capture-migrations');

            $this->publishes([
                __DIR__.'/../../resources/lang' => $this->app->langPath('vendor/ip-capture'),
            ], 'ip-capture-lang');
        }
    }
}
