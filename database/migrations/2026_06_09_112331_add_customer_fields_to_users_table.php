<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }

            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'customer'])
                    ->default('customer')
                    ->after('password');
            }

            if (!Schema::hasColumn('users', 'total_spent')) {
                $table->decimal('total_spent', 15, 2)
                    ->default(0)
                    ->after('role');
            }

            if (!Schema::hasColumn('users', 'membership_level')) {
                $table->enum('membership_level', ['bronze', 'silver', 'gold'])
                    ->default('bronze')
                    ->after('total_spent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = [
            'phone',
            'address',
            'role',
            'total_spent',
            'membership_level',
        ];

        $existingColumns = array_filter($columns, function ($column) {
            return Schema::hasColumn('users', $column);
        });

        if (!empty($existingColumns)) {
            Schema::table('users', function (Blueprint $table) use ($existingColumns) {
                $table->dropColumn($existingColumns);
            });
        }
    }
};