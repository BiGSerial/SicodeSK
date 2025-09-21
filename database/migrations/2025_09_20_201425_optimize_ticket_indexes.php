<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // Índice para filtros frequentes: área + created_at (ordenar/filtrar por mês)
        Schema::table('tickets', function () {
            DB::statement("CREATE INDEX IF NOT EXISTS idx_tickets_area_created_at ON tickets (area_id, created_at DESC)");
        });

        // Índice parcial para tickets abertos (útil para filas/dashboards)
        Schema::table('tickets', function () {
            DB::statement("CREATE INDEX IF NOT EXISTS idx_tickets_open ON tickets (area_id, priority, sla_due_at) WHERE status = 'open'");
        });

        // Opcional: checagem de formato do code
        DB::statement("
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1
                    FROM pg_constraint
                    WHERE conname = 'tickets_code_format_chk'
                ) THEN
                    ALTER TABLE tickets
                    ADD CONSTRAINT tickets_code_format_chk
                    CHECK (code ~ '^[A-Z0-9]{2,10}-[0-9]{4}-[0-9]{4}$');
                END IF;
            END$$;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS idx_tickets_area_created_at");
        DB::statement("DROP INDEX IF EXISTS idx_tickets_open");
        DB::statement("
            DO $$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM pg_constraint WHERE conname = 'tickets_code_format_chk'
                ) THEN
                    ALTER TABLE tickets DROP CONSTRAINT tickets_code_format_chk;
                END IF;
            END$$;
        ");
    }
};
