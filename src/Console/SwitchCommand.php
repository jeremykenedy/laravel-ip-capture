<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Console;

use Illuminate\Console\Command;
use Jeremykenedy\LaravelIpCapture\Console\Concerns\HandlesFrameworkSetup;

use function Laravel\Prompts\info;

class SwitchCommand extends Command
{
    use HandlesFrameworkSetup;

    protected $signature = 'ip-capture:switch
        {--css= : CSS framework (tailwind, bootstrap5, bootstrap4)}
        {--frontend= : Frontend framework (blade, livewire, vue, react, svelte)}';

    protected $description = 'Switch the CSS and/or frontend framework for Laravel IP Capture';

    public function handle(): int
    {
        $css = $this->option('css');
        $frontend = $this->option('frontend');

        if (!$css && !$frontend) {
            $this->error('Provide at least one of --css or --frontend.');
            $this->line('');
            $this->line('  Examples:');
            $this->line('    php artisan ip-capture:switch --css=bootstrap5');
            $this->line('    php artisan ip-capture:switch --frontend=livewire');
            $this->line('    php artisan ip-capture:switch --css=tailwind --frontend=vue');

            return self::FAILURE;
        }

        if ($css && !$this->validateCssFramework($css)) {
            return self::FAILURE;
        }

        if ($frontend && !$this->validateFrontend($frontend)) {
            return self::FAILURE;
        }

        if ($css) {
            $this->setCssFramework($css);
            info("IP Capture CSS framework switched to: {$css}");
        }

        if ($frontend) {
            $this->setFrontendFramework($frontend);
            info("IP Capture frontend switched to: {$frontend}");
        }

        info('Run: php artisan view:clear');

        return self::SUCCESS;
    }
}
