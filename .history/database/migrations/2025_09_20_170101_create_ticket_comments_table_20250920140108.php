<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ticket_comments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $t->uuid('author_sicode_id');
            $t->text('body');
            $t->json('meta')->nullable(); // mentions, flags, etc.
            $t->timestamps();

            $t->index(['ticket_id', 'created_at']);
            $t->index(['author_sicode_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_comments');
    }
};
