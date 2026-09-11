<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Console;

use Illuminate\Console\Command;
use Jeremykenedy\LaravelIpCapture\Console\Concerns\HandlesFrameworkSetup;
use Jeremykenedy\LaravelIpCapture\Console\Concerns\HasInstallPrompts;

use function Laravel\Prompts\info;

class UpdateCommand extends Command
{
    use HandlesFrameworkSetup;
    use HasInstallPrompts;

    protected $signature = 'ip-capture:update
        {--css= : CSS framework (tailwind, bootstrap5, bootstrap4)}
        {--frontend= : Frontend framework (blade, livewire, vue, react, svelte)}';

    protected $description = 'Update the CSS and/or frontend framework for Laravel IP Capture';

    public function handle(): int
    {
        $this->renderBanner('IP CAPTURE');

        if (!$this->isInstalled()) {
            $this->warn('  IP Capture is not installed yet.');
            $this->newLine();
            $this->line('  Run the install command first:');
            $this->line('    <comment>php artisan ip-capture:install</comment>');

            return self::FAILURE;
        }

        $css = $this->suppliedOption('css');
        $frontend = $this->suppliedOption('frontend');

        if ($css === null && $frontend === null) {
            $result = $this->promptFrameworks();

            if ($result === false) {
                return self::FAILURE;
            }

            $css = $result['css'];
            $frontend = $result['frontend'];
        } else {
            if ($css !== null && !$this->validateCssFramework($css)) {
                return self::FAILURE;
            }

            if ($frontend !== null && !$this->validateFrontend($frontend)) {
                return self::FAILURE;
            }
        }

        if ($css !== null) {
            $this->setCssFramework($css);
            info("CSS framework updated to: {$css}");

            // Only a framework change makes a published view stale, and
            // republishing overwrites whatever the application edited.
            // The framework specific tag, because the plain one resolves the
            // framework again in whatever process runs it.
            info("Republish the views: php artisan vendor:publish --tag=ip-capture-views-{$css} --force");
        }

        if ($frontend !== null) {
            $this->setFrontendFramework($frontend);
            $this->publishFrontendFor($frontend);
            info("Frontend updated to: {$frontend}");
        }

        info('Run: php artisan view:clear');

        return self::SUCCESS;
    }

    protected function isInstalled(): bool
    {
        return file_exists(config_path('ip-capture.php'));
    }
}
