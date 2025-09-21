<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function ($t) {
            $t->index(['area_id', 'status']);
            $t->index(['requester_sicode_id']);
            $t->index(['executor_sicode_id']);
            $t->index(['priority_id']);
            $t->index(['sla_due_at']);
        });

        // Postgres CHECKs (opcional)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE tickets
                ADD CONSTRAINT tickets_status_check
                CHECK (status IN ('open','in_progress','resolved','closed','paused'));");
        }
    }

    public function down(): void
    {
        // Remover índices e checks (ignorar erros se já não existirem)
        try {
            DB::statement("ALTER TABLE tickets DROP CONSTRAINT tickets_status_check");
        } catch (\Throwable $e) {
        }

        Schema::table('tickets', function ($t) {
            $t->dropIndex(['area_id', 'status']);
            $t->dropIndex(['requester_sicode_id']);
            $t->dropIndex(['executor_sicode_id']);
            $t->dropIndex(['priority_id']);
            $t->dropIndex(['sla_due_at']);
        });
    }
};
