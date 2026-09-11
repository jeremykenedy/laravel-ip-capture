<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Jeremykenedy\LaravelIpCapture\Console\InstallCommand;
use Jeremykenedy\LaravelIpCapture\Console\SwitchCommand;
use Jeremykenedy\LaravelIpCapture\Console\UpdateCommand;
use Jeremykenedy\LaravelIpCapture\Contracts\IpResolverInterface;
use Jeremykenedy\LaravelIpCapture\Services\IpResolver;
use Jeremykenedy\LaravelIpCapture\Support\IpCapture;

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

        $this->registerViews();
        $this->registerComponents();

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
            $this->registerPublishing();
        }
    }

    /**
     * Register the view namespace for the active CSS framework.
     *
     * Lookup falls back to the Tailwind views so a framework that ships no
     * override still resolves, and each framework also gets its own namespace
     * for rendering one specific framework on demand.
     */
    protected function registerViews(): void
    {
        if (!IpCapture::viewsEnabled()) {
            return;
        }

        $namespace = IpCapture::VIEW_NAMESPACE;

        $this->loadViewsFrom(IpCapture::viewPaths(IpCapture::cssFramework()), $namespace);

        foreach (IpCapture::CSS_FRAMEWORKS as $framework) {
            $this->loadViewsFrom(IpCapture::viewPaths($framework), $namespace.'-'.$framework);
        }
    }

    protected function registerComponents(): void
    {
        if (!IpCapture::viewsEnabled()) {
            return;
        }

        Blade::componentNamespace('Jeremykenedy\\LaravelIpCapture\\Components', IpCapture::VIEW_NAMESPACE);
    }

    protected function registerCommands(): void
    {
        $this->commands([
            InstallCommand::class,
            UpdateCommand::class,
            SwitchCommand::class,
        ]);
    }

    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__.'/../../config/ip-capture.php' => config_path('ip-capture.php'),
        ], 'ip-capture-config');

        $this->publishes([
            __DIR__.'/../../database/migrations/' => database_path('migrations'),
        ], 'ip-capture-migrations');

        $this->publishes([
            __DIR__.'/../../resources/lang' => $this->app->langPath('vendor/ip-capture'),
        ], 'ip-capture-lang');

        // Published flat so Laravel's namespace override picks the files up.
        $views = IpCapture::viewsPath();

        $this->publishes([
            $views.'/'.IpCapture::cssFramework().'/blade' => resource_path('views/vendor/ip-capture'),
        ], 'ip-capture-views');

        // A tag per framework, so the installer can publish the framework it
        // just selected rather than the one that was active at boot.
        foreach (IpCapture::CSS_FRAMEWORKS as $framework) {
            $this->publishes([
                $views.'/'.$framework.'/blade' => resource_path('views/vendor/ip-capture'),
            ], 'ip-capture-views-'.$framework);
        }

        $this->publishes([
            __DIR__.'/../../resources/js' => resource_path('js/vendor/ip-capture'),
        ], 'ip-capture-js');

        $this->publishes([
            __DIR__.'/../../resources/stubs/livewire/IpTable.php'        => app_path('Livewire/IpTable.php'),
            __DIR__.'/../../resources/stubs/livewire/ip-table.blade.php' => resource_path('views/livewire/ip-table.blade.php'),
        ], 'ip-capture-livewire');
    }
}
