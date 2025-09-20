public function up(): void
{
    if (!Schema::hasTable('ticket_attachments')) {
        Schema::create('ticket_attachments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $t->uuid('uploader_sicode_id');
            $t->string('filename', 255);
            $t->string('disk', 64)->default('local');
            $t->string('path', 1024);
            $t->string('mime', 190)->nullable();
            $t->unsignedBigInteger('size_bytes')->nullable();
            $t->timestamps();

            $t->index(['ticket_id', 'created_at']);
        });
    } else {
        // Opcional: garantir colunas/índices se a tabela foi criada “pela metade”
        Schema::table('ticket_attachments', function (Blueprint $t) {
            if (!Schema::hasColumn('ticket_attachments', 'ticket_id')) {
                $t->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
                $t->index(['ticket_id', 'created_at']);
            }
            if (!Schema::hasColumn('ticket_attachments', 'uploader_sicode_id')) {
                $t->uuid('uploader_sicode_id')->nullable();
            }
            if (!Schema::hasColumn('ticket_attachments', 'filename')) {
                $t->string('filename', 255)->nullable();
            }
            if (!Schema::hasColumn('ticket_attachments', 'disk')) {
                $t->string('disk', 64)->default('local');
            }
            if (!Schema::hasColumn('ticket_attachments', 'path')) {
                $t->string('path', 1024)->nullable();
            }
            if (!Schema::hasColumn('ticket_attachments', 'mime')) {
                $t->string('mime', 190)->nullable();
            }
            if (!Schema::hasColumn('ticket_attachments', 'size_bytes')) {
                $t->unsignedBigInteger('size_bytes')->nullable();
            }
        });
    }
}
