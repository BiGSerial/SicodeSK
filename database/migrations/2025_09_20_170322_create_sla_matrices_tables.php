<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('sla_matrices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $t->foreignId('ticket_type_id')->nullable()->constrained('ticket_types')->nullOnDelete();
            $t->unsignedInteger('low_minutes')->default(48 * 60);
            $t->unsignedInteger('medium_minutes')->default(24 * 60);
            $t->unsignedInteger('high_minutes')->default(8 * 60);
            $t->unsignedInteger('urgent_minutes')->default(4 * 60);
            $t->timestamps();

            $t->unique(['area_id', 'ticket_type_id']); // 1 por área/tipo
            $t->index(['area_id']);
        });

        Schema::create('sla_overrides', function (Blueprint $t) {
            $t->id();
            $t->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $t->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $t->unsignedInteger('override_minutes');
            $t->text('reason')->nullable();
            $t->uuid('set_by_sicode_id'); // manager/admin
            $t->timestamps();

            $t->unique(['ticket_id']);
            $t->index(['area_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_overrides');
        Schema::dropIfExists('sla_matrices');
    }
};
