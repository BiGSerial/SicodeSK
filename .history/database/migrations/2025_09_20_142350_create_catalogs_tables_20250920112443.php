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
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $t->string('nome');
            $t->boolean('ativa')->default(true);
            $t->timestamps();
        });
        
        Schema::create('subcategories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $t->string('nome');
            $t->boolean('ativa')->default(true);
            $t->timestamps();
        });

        Schema::create('ticket_types', function (Blueprint $t) {
            $t->id();
            $t->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $t->string('nome');
            $t->boolean('ativa')->default(true);
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogs_tables');
    }
};
