<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ticket_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $t->uuid('assignee_sicode_id');          // executor
            $t->uuid('assigned_by_sicode_id');       // manager/admin
            $t->timestamp('assigned_at')->useCurrent();
            $t->timestamp('unassigned_at')->nullable();
            $t->string('note', 240)->nullable();

            $t->index(['ticket_id', 'assigned_at']);
            $t->index(['assignee_sicode_id', 'assigned_at']);
        });

        // ponteiro rápido do "atual" no próprio tickets (opcional, se não quiser usar o executor_sicode_id):
        if (!Schema::hasColumn('tickets', 'current_assignment_id')) {
            Schema::table('tickets', function (Blueprint $t) {
                $t->foreignId('current_assignment_id')->nullable()->constrained('ticket_assignments')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tickets', 'current_assignment_id')) {
            Schema::table('tickets', function (Blueprint $t) {
                $t->dropConstrainedForeignId('current_assignment_id');
            });
        }
        Schema::dropIfExists('ticket_assignments');
    }
};
