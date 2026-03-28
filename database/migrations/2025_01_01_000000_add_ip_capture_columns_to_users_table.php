<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = config('ip-capture.columns', [
                'signup_ip_address'              => true,
                'signup_confirmation_ip_address' => true,
                'signup_sm_ip_address'           => true,
                'admin_ip_address'               => true,
                'updated_ip_address'             => true,
                'deleted_ip_address'             => true,
            ]);

            $afterColumn = 'password';

            $orderedColumns = [
                'signup_ip_address',
                'signup_confirmation_ip_address',
                'signup_sm_ip_address',
                'admin_ip_address',
                'updated_ip_address',
                'deleted_ip_address',
            ];

            foreach ($orderedColumns as $col) {
                if (($columns[$col] ?? false) && !Schema::hasColumn('users', $col)) {
                    $table->string($col, 64)->nullable()->after($afterColumn);
                }
                if (Schema::hasColumn('users', $col)) {
                    $afterColumn = $col;
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'signup_ip_address',
                'signup_confirmation_ip_address',
                'signup_sm_ip_address',
                'admin_ip_address',
                'updated_ip_address',
                'deleted_ip_address',
            ];

            $dropColumns = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if (count($dropColumns) > 0) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
