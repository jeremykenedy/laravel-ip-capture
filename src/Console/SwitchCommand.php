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
        $css = $this->suppliedOption('css');
        $frontend = $this->suppliedOption('frontend');

        if ($css === null && $frontend === null) {
            $this->error('Provide at least one of --css or --frontend.');
            $this->line('');
            $this->line('  Examples:');
            $this->line('    php artisan ip-capture:switch --css=bootstrap5');
            $this->line('    php artisan ip-capture:switch --frontend=livewire');
            $this->line('    php artisan ip-capture:switch --css=tailwind --frontend=vue');

            return self::FAILURE;
        }

        if ($css !== null && !$this->validateCssFramework($css)) {
            return self::FAILURE;
        }

        if ($frontend !== null && !$this->validateFrontend($frontend)) {
            return self::FAILURE;
        }

        if ($css !== null) {
            $this->setCssFramework($css);
            info("IP Capture CSS framework switched to: {$css}");

            // A view published earlier sits in front of the package views
            // whatever the framework is set to, so it has to be replaced.
            // The framework specific tag, because the plain one resolves the
            // framework again in whatever process runs it.
            info("Republish the views: php artisan vendor:publish --tag=ip-capture-views-{$css} --force");
        }

        if ($frontend !== null) {
            $this->setFrontendFramework($frontend);
            info("IP Capture frontend switched to: {$frontend}");

            foreach ($this->publishHintsFor($frontend) as $hint) {
                info($hint);
            }
        }

        info('Run: php artisan view:clear');

        return self::SUCCESS;
    }
}
