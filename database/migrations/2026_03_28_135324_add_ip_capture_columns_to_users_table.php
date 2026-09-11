<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Jeremykenedy\LaravelIpCapture\Support\IpCapture;

return new class() extends Migration {
    /**
     * The file name this migration shipped under up to version 1.1.
     */
    private const LEGACY_NAME = '2025_01_01_000000_add_ip_capture_columns_to_users_table';

    public function up(): void
    {
        $table = IpCapture::table();

        if (!Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            $after = IpCapture::afterColumn();
            $length = IpCapture::columnLength();

            foreach ($this->targetColumns() as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    $blueprint->string($column, $length)->nullable()->after($after);
                }

                $after = $column;
            }
        });
    }

    public function down(): void
    {
        $table = IpCapture::table();

        if (!Schema::hasTable($table)) {
            return;
        }

        // The columns were added under the old file name in installs that
        // predate 1.2, where this migration did not create them and must not
        // drop them.
        if ($this->appliedUnderLegacyName()) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            $existing = array_values(array_filter(
                $this->targetColumns(),
                fn (string $column): bool => Schema::hasColumn($table, $column),
            ));

            if ($existing !== []) {
                $blueprint->dropColumn($existing);
            }
        });
    }

    private function appliedUnderLegacyName(): bool
    {
        $repository = app('migration.repository');

        if (!$repository instanceof MigrationRepositoryInterface || !$repository->repositoryExists()) {
            return false;
        }

        return in_array(self::LEGACY_NAME, $repository->getRan(), true);
    }

    /**
     * The enabled columns, shipped ones first, then anything added to config.
     *
     * @return list<string>
     */
    private function targetColumns(): array
    {
        $configured = IpCapture::columns();

        if ($configured === []) {
            return IpCapture::DEFAULT_COLUMNS;
        }

        $enabled = IpCapture::enabledColumns();

        $ordered = array_values(array_intersect(IpCapture::DEFAULT_COLUMNS, $enabled));
        $custom = array_values(array_diff($enabled, IpCapture::DEFAULT_COLUMNS));

        return array_merge($ordered, $custom);
    }
};
