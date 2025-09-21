<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('areas', function (Blueprint $t) {
            if (!Schema::hasColumn('areas', 'sigla')) {
                $t->string('sigla', 10)->unique()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('areas', function (Blueprint $t) {
            if (Schema::hasColumn('areas', 'sigla')) {
                $t->dropUnique(['sigla']);
                $t->dropColumn('sigla');
            }
        });
    }
};
