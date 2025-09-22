<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            if (!Schema::hasColumn('workflows', 'ticket_type_id')) {
                $table->foreignId('ticket_type_id')->nullable()->constrained('ticket_types')->nullOnDelete();
            }

            if (!Schema::hasColumn('workflows', 'category_id')) {
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            if (Schema::hasColumn('workflows', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }

            if (Schema::hasColumn('workflows', 'ticket_type_id')) {
                $table->dropConstrainedForeignId('ticket_type_id');
            }
        });
    }
};
