<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Console\Concerns;

use Jeremykenedy\LaravelIpCapture\Support\IpCapture;

trait HandlesFrameworkSetup
{
    protected function getCssOption(): string
    {
        $css = $this->option('css');

        return is_string($css) && $css !== '' ? $css : IpCapture::cssFramework();
    }

    protected function getFrontendOption(): string
    {
        $frontend = $this->option('frontend');

        return is_string($frontend) && $frontend !== '' ? $frontend : IpCapture::frontend();
    }

    /**
     * An option the caller actually supplied.
     *
     * Truthiness is not enough: a value of "0" is invalid input to report, not
     * an absent flag to fill in with a default.
     */
    protected function suppliedOption(string $name): ?string
    {
        $value = $this->option($name);

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * Validate a CSS framework, reporting the supported values on failure.
     */
    protected function validateCssFramework(string $css): bool
    {
        if (IpCapture::isValidCssFramework($css)) {
            return true;
        }

        $this->error("Invalid CSS framework: {$css}. Valid: ".implode(', ', IpCapture::CSS_FRAMEWORKS));

        return false;
    }

    /**
     * Validate a frontend, reporting the supported values on failure.
     */
    protected function validateFrontend(string $frontend): bool
    {
        if (IpCapture::isValidFrontend($frontend)) {
            return true;
        }

        $this->error("Invalid frontend: {$frontend}. Valid: ".implode(', ', IpCapture::FRONTENDS));

        return false;
    }

    protected function updateEnvValue(string $key, string $value): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $path = base_path('.env');

        if (!file_exists($path)) {
            $this->warn("  No .env file, so {$key}={$value} was not persisted.");
            $this->line('  Set it in config/ip-capture.php instead.');

            return;
        }

        $content = file_get_contents($path);

        if ($content === false) {
            return;
        }

        if (preg_match("/^{$key}=/m", $content) === 1) {
            $content = (string) preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
        } else {
            $content = rtrim($content, "\n")."\n{$key}={$value}\n";
        }

        if (file_put_contents($path, $content) === false) {
            $this->warn("  Could not write to .env, so {$key}={$value} was not persisted.");
            $this->line('  Set it in config/ip-capture.php instead.');
        }
    }

    protected function setCssFramework(string $css): void
    {
        $this->updateEnvValue('IP_CAPTURE_CSS', $css);

        config(['ip-capture.css_framework' => $css]);

        $this->clearCaches();
    }

    protected function setFrontendFramework(string $frontend): void
    {
        $this->updateEnvValue('IP_CAPTURE_FRONTEND', $frontend);

        config(['ip-capture.frontend' => $frontend]);

        $this->clearCaches();
    }

    protected function publishViewsFor(string $css): void
    {
        $this->callSilently('vendor:publish', [
            '--tag'   => 'ip-capture-views-'.$css,
            '--force' => true,
        ]);
    }

    /**
     * What a switch leaves for the reader to publish, since it changes
     * configuration without touching files.
     *
     * @return list<string>
     */
    protected function publishHintsFor(string $frontend): array
    {
        if (in_array($frontend, IpCapture::JS_FRONTENDS, true)) {
            return ['Publish the components: php artisan vendor:publish --tag=ip-capture-js'];
        }

        if ($frontend === 'livewire') {
            return ['Publish the component: php artisan vendor:publish --tag=ip-capture-livewire'];
        }

        return [];
    }

    /**
     * Publish the files a frontend needs.
     *
     * Only a reinstall overwrites, because these are files an application is
     * expected to edit once they are published.
     */
    protected function publishFrontendFor(string $frontend, bool $force = false): void
    {
        $tag = match (true) {
            in_array($frontend, IpCapture::JS_FRONTENDS, true) => 'ip-capture-js',
            $frontend === 'livewire'                           => 'ip-capture-livewire',
            default                                            => null,
        };

        if ($tag === null) {
            return;
        }

        $this->callSilently('vendor:publish', array_filter([
            '--tag'   => $tag,
            '--force' => $force,
        ]));
    }

    protected function clearCaches(): void
    {
        $this->callSilently('config:clear');
        $this->callSilently('view:clear');
    }
}
