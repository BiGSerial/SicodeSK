<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->foreignId('area_id')->constrained('areas');
            $t->foreignId('ticket_type_id')->constrained('ticket_types');
            $t->foreignId('category_id')->nullable()->constrained('categories');
            $t->foreignId('subcategory_id')->nullable()->constrained('subcategories');
            $t->foreignId('workflow_id')->nullable()->constrained('workflows')->nullOnDelete();
            $t->foreignId('step_id')->nullable()->constrained('workflow_steps');

            $t->string('priority')->default('medium');
            $t->string('title');
            $t->longText('description')->nullable();
            $t->string('status')->default('open');

            $t->uuid('requester_sicode_id');
            $t->uuid('manager_sicode_id')->nullable();
            $t->uuid('executor_sicode_id')->nullable();

            $t->timestamp('sla_due_at')->nullable();
            $t->boolean('is_late')->default(false);

            $t->timestamps();
        });

        Schema::create('ticket_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $t->uuid('actor_sicode_id');
            $t->string('type');
            $t->json('payload_json')->nullable();
            $t->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_events');
        Schema::dropIfExists('tickets');
    }
};
