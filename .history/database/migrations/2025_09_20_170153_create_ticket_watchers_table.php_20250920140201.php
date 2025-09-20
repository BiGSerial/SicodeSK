<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ticket_watchers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $t->uuid('sicode_id');
            $t->timestamps();

            $t->unique(['ticket_id', 'sicode_id']);
            $t->index(['sicode_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_watchers');
    }
};
