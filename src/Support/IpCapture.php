<?php

declare(strict_types=1);

namespace Jeremykenedy\LaravelIpCapture\Support;

use InvalidArgumentException;

/**
 * Central resolution point for the package configuration.
 *
 * Reading configuration through this class keeps the resolver, the trait, the
 * console commands, the components and the migration in agreement.
 */
class IpCapture
{
    public const CSS_FRAMEWORKS = ['tailwind', 'bootstrap5', 'bootstrap4'];

    public const FRONTENDS = ['blade', 'livewire', 'vue', 'react', 'svelte'];

    public const CSS_LABELS = [
        'tailwind'   => 'Tailwind CSS v4',
        'bootstrap5' => 'Bootstrap 5',
        'bootstrap4' => 'Bootstrap 4',
    ];

    public const FRONTEND_LABELS = [
        'blade'    => 'Blade',
        'livewire' => 'Livewire 3',
        'vue'      => 'Vue 3',
        'react'    => 'React 18/19',
        'svelte'   => 'Svelte 4/5',
    ];

    public const JS_FRONTENDS = ['vue', 'react', 'svelte'];

    public const VIEW_NAMESPACE = 'ip-capture';

    /**
     * The columns shipped since 1.0, in migration order.
     */
    public const DEFAULT_COLUMNS = [
        'signup_ip_address',
        'signup_confirmation_ip_address',
        'signup_sm_ip_address',
        'admin_ip_address',
        'updated_ip_address',
        'deleted_ip_address',
    ];

    /**
     * Model events that fire before Eloquent writes the row.
     *
     * Capture assigns an attribute, so an event that fires after the write has
     * nothing left to affect and is ignored rather than silently doing nothing.
     */
    public const CAPTURABLE_EVENTS = ['saving', 'creating', 'updating', 'deleting', 'restoring'];

    /**
     * Kept as a constant so the shipped default survives a published config
     * file written before the headers key existed.
     */
    public const DEFAULT_HEADERS = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR',
    ];

    public static function enabled(): bool
    {
        return (bool) config('ip-capture.enabled', true);
    }

    public static function nullIp(): string
    {
        $nullIp = config('ip-capture.null_ip', '0.0.0.0');

        return is_string($nullIp) && $nullIp !== '' ? $nullIp : '0.0.0.0';
    }

    /**
     * @return array<string, bool>
     */
    public static function columns(): array
    {
        $columns = config('ip-capture.columns', []);

        if (!is_array($columns)) {
            return [];
        }

        $resolved = [];

        foreach ($columns as $column => $enabled) {
            if (is_string($column) && $column !== '') {
                $resolved[$column] = $enabled === true;
            }
        }

        return $resolved;
    }

    /**
     * @return list<string>
     */
    public static function enabledColumns(): array
    {
        return array_keys(array_filter(self::columns()));
    }

    /**
     * Whether the columns key is present at all, which is not the same as it
     * being present and empty.
     */
    public static function columnsConfigured(): bool
    {
        return config()->has('ip-capture.columns');
    }

    public static function columnEnabled(string $column): bool
    {
        return (self::columns()[$column] ?? false) === true;
    }

    /**
     * @return list<string>
     */
    public static function headers(): array
    {
        $headers = config('ip-capture.headers', self::DEFAULT_HEADERS);

        if (!is_array($headers)) {
            return self::DEFAULT_HEADERS;
        }

        $resolved = array_values(array_filter(
            $headers,
            static fn (mixed $header): bool => is_string($header) && $header !== '',
        ));

        return $resolved === [] ? self::DEFAULT_HEADERS : $resolved;
    }

    public static function trustProxies(): bool
    {
        return (bool) config('ip-capture.trust_proxies', true);
    }

    public static function shouldHash(): bool
    {
        return (bool) config('ip-capture.hash', false);
    }

    public static function hashAlgo(): string
    {
        $algo = config('ip-capture.hash_algo', 'sha256');

        return is_string($algo) && $algo !== '' ? $algo : 'sha256';
    }

    public static function hashSalt(): string
    {
        $salt = config('ip-capture.hash_salt', '');

        return is_string($salt) ? $salt : '';
    }

    public static function shouldAnonymize(): bool
    {
        return (bool) config('ip-capture.anonymize', false);
    }

    public static function autoCaptureEnabled(): bool
    {
        return (bool) config('ip-capture.auto_capture.enabled', false);
    }

    /**
     * Model events wired to a column, keyed by event name.
     *
     * @return array<string, string>
     */
    public static function autoCaptureEvents(): array
    {
        $events = config('ip-capture.auto_capture.events', []);

        if (!is_array($events)) {
            return [];
        }

        $resolved = [];

        foreach ($events as $event => $column) {
            if (!is_string($event) || !is_string($column) || $column === '') {
                continue;
            }

            if (in_array($event, self::CAPTURABLE_EVENTS, true)) {
                $resolved[$event] = $column;
            }
        }

        return $resolved;
    }

    public static function table(): string
    {
        $table = config('ip-capture.table', 'users');

        return is_string($table) && $table !== '' ? $table : 'users';
    }

    public static function afterColumn(): string
    {
        $column = config('ip-capture.after_column', 'password');

        return is_string($column) && $column !== '' ? $column : 'password';
    }

    public static function columnLength(): int
    {
        $length = (int) config('ip-capture.column_length', 64);

        return $length > 0 ? $length : 64;
    }

    /**
     * The active CSS framework, inherited from laravel-ui-kit when unset.
     */
    public static function cssFramework(): string
    {
        $css = config('ip-capture.css_framework') ?? config('ui-kit.css_framework', 'tailwind');

        return self::isValidCssFramework($css) ? (string) $css : 'tailwind';
    }

    /**
     * The active frontend, inherited from laravel-ui-kit when unset.
     */
    public static function frontend(): string
    {
        $frontend = config('ip-capture.frontend') ?? config('ui-kit.frontend', 'blade');

        return self::isValidFrontend($frontend) ? (string) $frontend : 'blade';
    }

    public static function isValidCssFramework(mixed $css): bool
    {
        return is_string($css) && in_array($css, self::CSS_FRAMEWORKS, true);
    }

    public static function isValidFrontend(mixed $frontend): bool
    {
        return is_string($frontend) && in_array($frontend, self::FRONTENDS, true);
    }

    /**
     * The directory the shipped views live in.
     */
    public static function viewsPath(): string
    {
        return dirname(__DIR__, 2).'/resources/views';
    }

    /**
     * View lookup order for a framework, falling back to the Tailwind views so
     * a framework that ships no override of a given view still resolves.
     *
     * @return list<string>
     */
    public static function viewPaths(string $framework): array
    {
        $base = self::viewsPath();

        return [
            $base.'/'.$framework.'/blade',
            $base.'/tailwind/blade',
        ];
    }

    public static function viewsEnabled(): bool
    {
        return (bool) config('ip-capture.views.enabled', true);
    }

    public static function shouldMaskDisplay(): bool
    {
        return (bool) config('ip-capture.display.mask', false);
    }

    public static function emptyLabel(): string
    {
        $label = config('ip-capture.display.empty_label', '-');

        return is_string($label) ? $label : '-';
    }

    /**
     * The translated label for a column, falling back to a readable name.
     */
    public static function columnLabel(string $column): string
    {
        $key = self::VIEW_NAMESPACE.'::ip-capture.columns.'.$column;
        $label = trans($key);

        if (is_string($label) && $label !== $key) {
            return $label;
        }

        return ucfirst(str_replace(['_sm_', '_'], [' social ', ' '], $column));
    }

    /**
     * Apply the storage pipeline to an address: anonymize, then hash.
     *
     * Every path that writes a column goes through here, so a supplied
     * address is stored under the same rules as a resolved one.
     */
    public static function prepare(string $ip): string
    {
        if (self::shouldAnonymize()) {
            $ip = self::anonymize($ip);
        }

        if (!self::shouldHash()) {
            return $ip;
        }

        $algo = self::hashAlgo();

        // hash() matches an algorithm name case insensitively while
        // hash_algos() lists them lowercase, so a setting of SHA256 has always
        // worked and has to keep working.
        $normalized = strtolower($algo);

        if (!in_array($normalized, hash_algos(), true)) {
            throw new InvalidArgumentException(
                "Unsupported hashing algorithm [{$algo}] configured in ip-capture.hash_algo."
            );
        }

        return hash($normalized, self::hashSalt().$ip);
    }

    /**
     * The null IP as it looks once stored, which is what a column holds when
     * nothing could be resolved.
     */
    public static function preparedNullIp(): string
    {
        return self::prepare(self::nullIp());
    }

    /**
     * Drop the host part of an address, keeping a /24 or a /64 network.
     */
    public static function anonymize(string $ip): string
    {
        $packed = @inet_pton($ip);

        if ($packed === false) {
            return $ip;
        }

        $network = strlen($packed) === 4
            ? substr($packed, 0, 3)."\0"
            : substr($packed, 0, 8).str_repeat("\0", 8);

        $anonymized = @inet_ntop($network);

        return $anonymized === false ? $ip : $anonymized;
    }

    /**
     * Hide the host part of an address for display without changing storage.
     */
    public static function mask(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            return substr($ip, 0, (int) strrpos($ip, '.') + 1).'xxx';
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            return self::anonymize($ip);
        }

        return $ip;
    }
}
