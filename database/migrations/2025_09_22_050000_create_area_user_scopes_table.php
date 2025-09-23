<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('area_user_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->uuid('sicode_id');
            $table->foreignId('ticket_type_id')->nullable()->constrained('ticket_types')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('subcategories')->nullOnDelete();
            $table->timestamps();

            $table->unique(['area_id', 'sicode_id', 'ticket_type_id', 'category_id', 'subcategory_id'], 'area_user_scopes_unique');
            $table->index(['area_id', 'ticket_type_id', 'category_id', 'subcategory_id'], 'area_user_scopes_lookup');
            $table->index(['sicode_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_user_scopes');
    }
};

