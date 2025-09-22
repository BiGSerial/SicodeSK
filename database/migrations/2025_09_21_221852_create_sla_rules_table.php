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
        Schema::create('sla_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('priority_id')->constrained('priorities')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('ticket_type_id')->nullable()->constrained('ticket_types')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('subcategories')->nullOnDelete();
            $table->unsignedInteger('increment_minutes')->default(0);
            $table->unsignedInteger('tolerance_minutes')->default(0);
            $table->boolean('pause_suspends')->default(false);
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['priority_id', 'area_id', 'ticket_type_id', 'category_id', 'subcategory_id'], 'sla_rules_scope_idx');
        });

        DB::statement(<<<SQL
            CREATE UNIQUE INDEX sla_rules_scope_unique
            ON sla_rules (
                priority_id,
                COALESCE(area_id, 0),
                COALESCE(ticket_type_id, 0),
                COALESCE(category_id, 0),
                COALESCE(subcategory_id, 0)
            )
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE sla_rules DROP CONSTRAINT IF EXISTS sla_rules_scope_unique');
        DB::statement('DROP INDEX IF EXISTS sla_rules_scope_unique');
        Schema::dropIfExists('sla_rules');
    }
};
