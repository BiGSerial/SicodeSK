<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // === AREAS ===
        Schema::create('areas', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->boolean('active')->default(true);
            $t->timestamps();

            $t->index(['active']);
            $t->unique(['name']); // se quiser permitir nomes únicos
        });

        // === AREA MEMBERSHIP (link SICODE users -> areas) ===
        Schema::create('area_user', function (Blueprint $t) {
            $t->id();
            $t->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $t->uuid('sicode_id'); // UUID do usuário do Sicode (MariaDB)
            $t->string('role_in_area', 32)->default('member'); // 'manager' | 'member'
            $t->timestamps();

            $t->unique(['area_id', 'sicode_id']);  // cada user único por área
            $t->index(['sicode_id']);
            $t->index(['role_in_area']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_user');
        Schema::dropIfExists('areas');
    }
};
