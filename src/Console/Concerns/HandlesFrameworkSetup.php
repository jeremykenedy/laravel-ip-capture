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

        file_put_contents($path, $content);
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

    protected function publishFrontendFor(string $frontend): void
    {
        if (in_array($frontend, IpCapture::JS_FRONTENDS, true)) {
            $this->callSilently('vendor:publish', ['--tag' => 'ip-capture-js', '--force' => true]);

            return;
        }

        if ($frontend === 'livewire') {
            $this->callSilently('vendor:publish', ['--tag' => 'ip-capture-livewire', '--force' => true]);
        }
    }

    protected function clearCaches(): void
    {
        $this->callSilently('config:clear');
        $this->callSilently('view:clear');
    }
}
