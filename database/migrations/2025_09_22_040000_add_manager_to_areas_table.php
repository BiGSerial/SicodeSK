<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            if (!Schema::hasColumn('areas', 'manager_sicode_id')) {
                $table->uuid('manager_sicode_id')->nullable()->after('active');
                $table->index('manager_sicode_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            if (Schema::hasColumn('areas', 'manager_sicode_id')) {
                $table->dropIndex(['manager_sicode_id']);
                $table->dropColumn('manager_sicode_id');
            }
        });
    }
};
