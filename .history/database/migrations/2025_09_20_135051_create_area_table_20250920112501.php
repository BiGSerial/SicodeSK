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
        Schema::create('areas', function (Blueprint $t) {
            $t->id();
            $t->string('nome');
            $t->boolean('ativa')->default(true);
            $t->timestamps();
        });

        Schema::create('area_user', function (Blueprint $t) {
            $t->id();
            $t->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $t->uuid('sicode_id'); // FK lógica para Sicode
            $t->enum('funcao_na_area', ['manager','member'])->default('member');
            $t->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_user');
        Schema::dropIfExists('areas');
    }
};
