<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaultPriorityId = DB::table('priorities')
            ->where('is_default', true)
            ->value('id');

        if (!$defaultPriorityId) {
            $defaultPriorityId = DB::table('priorities')->orderByDesc('weight')->value('id');
        }

        Schema::table('tickets', function (Blueprint $table) use ($defaultPriorityId) {
            $table->foreignId('priority_id')
                ->nullable()
                ->after('priority')
                ->default($defaultPriorityId)
                ->constrained('priorities')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        $mapping = DB::table('priorities')->pluck('id', 'slug');

        DB::table('tickets')->orderBy('id')->chunkById(500, function ($tickets) use ($mapping, $defaultPriorityId) {
            foreach ($tickets as $ticket) {
                $slug = is_string($ticket->priority) ? strtolower($ticket->priority) : null;
                $priorityId = $slug && isset($mapping[$slug]) ? $mapping[$slug] : $defaultPriorityId;

                DB::table('tickets')
                    ->where('id', $ticket->id)
                    ->update(['priority_id' => $priorityId]);
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('priority')->nullable()->after('priority_id');
        });

        DB::table('tickets')->orderBy('id')->chunkById(500, function ($tickets) {
            $priorityLookup = DB::table('priorities')->pluck('slug', 'id');

            foreach ($tickets as $ticket) {
                $slug = $ticket->priority_id ? ($priorityLookup[$ticket->priority_id] ?? null) : null;

                DB::table('tickets')
                    ->where('id', $ticket->id)
                    ->update(['priority' => $slug]);
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['priority_id']);
            $table->dropColumn('priority_id');
        });
    }
};
