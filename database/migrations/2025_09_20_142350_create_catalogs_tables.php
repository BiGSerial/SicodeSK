<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // === TICKET TYPES ===
        Schema::create('ticket_types', function (Blueprint $t) {
            $t->id();
            $t->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $t->string('name');
            $t->boolean('active')->default(true);
            $t->timestamps();

            $t->index(['area_id', 'active']);
            $t->unique(['area_id', 'name']); // não repete nome dentro da área
        });

        // === CATEGORIES ===
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $t->foreignId('ticket_type_id')->constrained('ticket_types')->cascadeOnDelete();
            $t->string('name');
            $t->boolean('active')->default(true);
            $t->timestamps();

            // índices e unicidade por tipo/área
            $t->index(['area_id', 'ticket_type_id', 'active']);
            $t->unique(['ticket_type_id', 'name']); // não repete nome dentro do mesmo tipo
        });

        // === SUBCATEGORIES ===
        Schema::create('subcategories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $t->string('name');
            $t->boolean('active')->default(true);
            $t->timestamps();

            $t->index(['category_id', 'active']);
            $t->unique(['category_id', 'name']); // não repete nome dentro da mesma categoria
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcategories');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('ticket_types');
    }
};
