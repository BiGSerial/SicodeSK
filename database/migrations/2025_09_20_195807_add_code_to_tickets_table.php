<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $t) {
            if (!Schema::hasColumn('tickets', 'code')) {
                $t->string('code', 32)->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $t) {
            if (Schema::hasColumn('tickets', 'code')) {
                $t->dropUnique(['code']);
                $t->dropColumn('code');
            }
        });
    }
};
