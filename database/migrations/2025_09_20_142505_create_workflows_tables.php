<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $t) {
            $t->id();
            $t->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $t->string('name');
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('workflow_steps', function (Blueprint $t) {
            $t->id();
            $t->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();
            $t->unsignedInteger('order'); // 1,2,3...
            $t->string('name');
            // string simples (pode virar enum real depois)
            $t->string('assign_rule')->default('manual'); // manual | manager | round_robin
            $t->unsignedInteger('sla_target_minutes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflows');
    }
};
