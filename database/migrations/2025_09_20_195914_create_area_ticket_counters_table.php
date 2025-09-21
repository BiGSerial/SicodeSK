<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('area_ticket_counters', function (Blueprint $t) {
            $t->id();
            $t->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $t->char('period', 4); // AAMM (ex.: 2509)
            $t->unsignedInteger('last_number')->default(0);
            $t->timestamps();

            $t->unique(['area_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_ticket_counters');
    }
};
