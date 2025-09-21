<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketEventSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::connection('pgsql')->table('ticket_events')->truncate();

        $events = [
            [
                'ticket_id'        => 1,
                'actor_sicode_id'  => '11111111-1111-1111-1111-111111111111',
                'type'             => 'created',
                'payload_json'     => json_encode(['priority' => 'medium']),
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'ticket_id'        => 2,
                'actor_sicode_id'  => '33333333-3333-3333-3333-333333333333',
                'type'             => 'assigned',
                'payload_json'     => json_encode(['executor' => '4444...']),
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];

        DB::connection('pgsql')->table('ticket_events')->insert($events);
    }
}
