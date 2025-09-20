<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('work_calendars', function (Blueprint $t) {
            $t->id();
            $t->string('name', 80);           // ex: "Default Business Hours"
            $t->json('workweek')->nullable(); // {"mon":{"start":"08:00","end":"17:00"}, ...}
            $t->timestamps();
            $t->unique(['name']);
        });

        Schema::create('work_calendar_holidays', function (Blueprint $t) {
            $t->id();
            $t->foreignId('work_calendar_id')->constrained('work_calendars')->cascadeOnDelete();
            $t->date('holiday_date');
            $t->string('label', 120)->nullable();
            $t->timestamps();

            $t->unique(['work_calendar_id', 'holiday_date']);
            $t->index(['holiday_date']);
        });

        // liga cada área a um calendário de trabalho
        if (!Schema::hasColumn('areas', 'work_calendar_id')) {
            Schema::table('areas', function (Blueprint $t) {
                $t->foreignId('work_calendar_id')->nullable()->constrained('work_calendars')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('areas', 'work_calendar_id')) {
            Schema::table('areas', function (Blueprint $t) {
                $t->dropConstrainedForeignId('work_calendar_id');
            });
        }
        Schema::dropIfExists('work_calendar_holidays');
        Schema::dropIfExists('work_calendars');
    }
};
