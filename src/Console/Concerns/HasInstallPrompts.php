<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Console\Concerns;

use Jeremykenedy\LaravelIpCapture\Support\IpCapture;

use function Laravel\Prompts\select;

trait HasInstallPrompts
{
    /**
     * Numeric glyph keys such as '2' arrive as integer keys, hence array-key.
     *
     * @var array<array-key, list<string>>
     */
    protected static array $font = [
        'A' => ['  ██  ', ' ████ ', '██  ██', '██████', '██  ██'],
        'B' => ['█████ ', '██  ██', '█████ ', '██  ██', '█████ '],
        'C' => [' ████ ', '██    ', '██    ', '██    ', ' ████ '],
        'D' => ['████  ', '██  ██', '██  ██', '██  ██', '████  '],
        'E' => ['██████', '██    ', '████  ', '██    ', '██████'],
        'F' => ['██████', '██    ', '████  ', '██    ', '██    '],
        'G' => [' ████ ', '██    ', '██ ███', '██  ██', ' ████ '],
        'H' => ['██  ██', '██  ██', '██████', '██  ██', '██  ██'],
        'I' => ['██████', '  ██  ', '  ██  ', '  ██  ', '██████'],
        'J' => ['   ███', '    ██', '    ██', '██  ██', ' ████ '],
        'K' => ['██  ██', '██ ██ ', '████  ', '██ ██ ', '██  ██'],
        'L' => ['██    ', '██    ', '██    ', '██    ', '██████'],
        'M' => ['██   ██', '███ ███', '██ █ ██', '██   ██', '██   ██'],
        'N' => ['██  ██', '███ ██', '██████', '██ ███', '██  ██'],
        'O' => [' ████ ', '██  ██', '██  ██', '██  ██', ' ████ '],
        'P' => ['█████ ', '██  ██', '█████ ', '██    ', '██    '],
        'Q' => [' ████ ', '██  ██', '██  ██', '██ ██ ', ' ██ ██'],
        'R' => ['█████ ', '██  ██', '█████ ', '██ ██ ', '██  ██'],
        'S' => [' ████ ', '██    ', ' ████ ', '    ██', ' ████ '],
        'T' => ['██████', '  ██  ', '  ██  ', '  ██  ', '  ██  '],
        'U' => ['██  ██', '██  ██', '██  ██', '██  ██', ' ████ '],
        'V' => ['██  ██', '██  ██', '██  ██', ' ████ ', '  ██  '],
        'W' => ['██   ██', '██   ██', '██ █ ██', '███ ███', '██   ██'],
        'X' => ['██  ██', ' ████ ', '  ██  ', ' ████ ', '██  ██'],
        'Y' => ['██  ██', ' ████ ', '  ██  ', '  ██  ', '  ██  '],
        'Z' => ['██████', '   ██ ', '  ██  ', ' ██   ', '██████'],
        '2' => [' ████ ', '    ██', ' ████ ', '██    ', '██████'],
        '-' => ['      ', '      ', ' ████ ', '      ', '      '],
        ' ' => ['   ', '   ', '   ', '   ', '   '],
    ];

    protected function renderBanner(string $name): void
    {
        $chars = str_split(strtoupper($name));
        $lines = ['', '', '', '', ''];

        foreach ($chars as $char) {
            $glyph = self::$font[$char] ?? self::$font[' '];
            for ($i = 0; $i < 5; $i++) {
                $lines[$i] .= $glyph[$i].' ';
            }
        }

        $this->newLine();

        $palettes = [
            ['34', '35', '94', '95', '96'],   // Blue/Purple/Cyan
            ['31', '91', '33', '93', '31'],   // Red/Yellow
            ['32', '92', '36', '96', '32'],   // Green/Cyan
            ['33', '93', '91', '31', '33'],   // Yellow/Red
            ['35', '95', '34', '94', '35'],   // Magenta/Blue
            ['36', '96', '92', '32', '36'],   // Cyan/Green
            ['91', '93', '92', '96', '94'],   // Bright rainbow
            ['95', '35', '34', '94', '96'],   // Pink to Cyan
        ];
        $colors = $palettes[array_rand($palettes)];

        foreach ($lines as $i => $line) {
            $color = $colors[$i % count($colors)];
            $this->line("  \033[{$color}m{$line}\033[0m");
        }

        $this->newLine();
    }

    /**
     * Run the stepped framework selection flow with back navigation and confirmation.
     *
     * @return array{css: string, frontend: string}|false
     */
    protected function promptFrameworks(): array|false
    {
        $css = $this->option('css');
        $frontend = $this->option('frontend');

        if ($css && $frontend) {
            if (!$this->validateSelection($css, $frontend)) {
                return false;
            }

            return ['css' => $css, 'frontend' => $frontend];
        }

        if ($this->option('no-interaction')) {
            return [
                'css'      => $css ?: IpCapture::cssFramework(),
                'frontend' => $frontend ?: IpCapture::frontend(),
            ];
        }

        while (true) {
            $cssResult = $this->promptCssFramework();
            if ($cssResult === false) {
                return false;
            }

            $frontendResult = $this->promptFrontendFramework();
            if ($frontendResult === false) {
                return false;
            }
            if ($frontendResult === '__back__') {
                continue;
            }

            $confirmation = $this->promptConfirmation($cssResult, $frontendResult);

            if ($confirmation === 'confirm') {
                return ['css' => $cssResult, 'frontend' => $frontendResult];
            }

            if ($confirmation === 'cancel') {
                $this->info('  Cancelled. No changes were made.');

                return false;
            }
        }
    }

    /**
     * Validate a pair of framework selections, reporting the first problem.
     */
    protected function validateSelection(string $css, string $frontend): bool
    {
        if (!IpCapture::isValidCssFramework($css)) {
            $this->error("Invalid CSS framework: {$css}. Use: ".implode(', ', IpCapture::CSS_FRAMEWORKS));

            return false;
        }

        if (!IpCapture::isValidFrontend($frontend)) {
            $this->error("Invalid frontend: {$frontend}. Use: ".implode(', ', IpCapture::FRONTENDS));

            return false;
        }

        return true;
    }

    protected function promptCssFramework(): string|false
    {
        $css = $this->option('css');

        if ($css) {
            if (!IpCapture::isValidCssFramework($css)) {
                $this->error("Invalid CSS framework: {$css}. Use: ".implode(', ', IpCapture::CSS_FRAMEWORKS));

                return false;
            }

            return $css;
        }

        if ($this->option('no-interaction')) {
            return IpCapture::cssFramework();
        }

        return select(
            label: 'Which CSS framework would you like to use?',
            options: IpCapture::CSS_LABELS,
            default: IpCapture::cssFramework(),
        );
    }

    protected function promptFrontendFramework(): string|false
    {
        $frontend = $this->option('frontend');

        if ($frontend) {
            if (!IpCapture::isValidFrontend($frontend)) {
                $this->error("Invalid frontend: {$frontend}. Use: ".implode(', ', IpCapture::FRONTENDS));

                return false;
            }

            return $frontend;
        }

        if ($this->option('no-interaction')) {
            return IpCapture::frontend();
        }

        return select(
            label: 'Which frontend would you like to use?',
            options: ['__back__' => "\033[90m< Back to CSS selection\033[0m"] + IpCapture::FRONTEND_LABELS,
            default: IpCapture::frontend(),
        );
    }

    protected function promptConfirmation(string $css, string $frontend): string
    {
        $this->newLine();
        $this->line("  \033[1mYour selections:\033[0m");
        $this->line("  \033[90mCSS:\033[0m       ".$this->cssLabel($css));
        $this->line("  \033[90mFrontend:\033[0m  ".$this->frontendLabel($frontend));
        $this->newLine();

        return select(
            label: 'Continue with these settings?',
            options: [
                'confirm' => 'Confirm and continue',
                'restart' => 'Start over',
                'cancel'  => 'Cancel and exit',
            ],
            default: 'confirm',
        );
    }

    protected function showSummary(string $packageName, string $css, string $frontend): void
    {
        $this->newLine();
        $this->line("  \033[32m{$packageName} installed successfully.\033[0m");
        $this->newLine();
        $this->line("  \033[90mCSS:\033[0m       ".$this->cssLabel($css));
        $this->line("  \033[90mFrontend:\033[0m  ".$this->frontendLabel($frontend));
        $this->newLine();
        $this->line('  Next steps:');
        $this->line("    1. Add the \033[33mCapturesIp\033[0m trait to your model");
        $this->line("    2. Run \033[33mphp artisan migrate\033[0m to add the IP columns");
        $this->line("    3. Drop \033[33m<x-ip-capture::ip-table :model=\"\$user\" />\033[0m into a view");

        if (in_array($frontend, IpCapture::JS_FRONTENDS, true)) {
            $this->line("    4. Run \033[33mnpm run build\033[0m");
        }

        $this->newLine();
    }

    protected function cssLabel(string $css): string
    {
        return IpCapture::CSS_LABELS[$css] ?? $css;
    }

    protected function frontendLabel(string $frontend): string
    {
        return IpCapture::FRONTEND_LABELS[$frontend] ?? $frontend;
    }
}
