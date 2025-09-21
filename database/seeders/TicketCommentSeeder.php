<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketCommentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::connection('pgsql')->table('ticket_comments')->insert([
            [
                'ticket_id' => 1,
                'author_sicode_id' => '11111111-1111-1111-1111-111111111111',
                'body' => 'Esse bug ocorre somente no Firefox, no Chrome está ok.',
                'meta' => json_encode(['mentions' => []]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ticket_id' => 2,
                'author_sicode_id' => '44444444-4444-4444-4444-444444444444',
                'body' => 'Já reiniciei o serviço de e-mail, mas o problema persiste.',
                'meta' => json_encode(['mentions' => []]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
