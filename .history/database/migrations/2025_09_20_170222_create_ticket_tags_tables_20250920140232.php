<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $t) {
            $t->id();
            $t->string('name', 50);
            $t->string('color', 24)->nullable(); // ex: bg-emerald-600
            $t->timestamps();
            $t->unique(['name']);
        });

        Schema::create('tag_ticket', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $t->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $t->timestamps();

            $t->unique(['tag_id', 'ticket_id']);
            $t->index(['ticket_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tag_ticket');
        Schema::dropIfExists('tags');
    }
};
