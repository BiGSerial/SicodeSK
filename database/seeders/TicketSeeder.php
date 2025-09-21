<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket; // importante!
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Limpa a tabela
        DB::connection('pgsql')->statement('TRUNCATE tickets RESTART IDENTITY CASCADE');

        // IDs necessários
        $workflowId = DB::connection('pgsql')->table('workflows')
            ->where('name', 'Default Dev Flow')
            ->value('id');

        $stepId = DB::connection('pgsql')->table('workflow_steps')
            ->where('workflow_id', $workflowId)
            ->orderBy('order')
            ->value('id');

        $priorityIds = DB::table('priorities')->pluck('id', 'slug');

        $priorityMedium = $priorityIds['medium'] ?? $priorityIds->first();
        $priorityUrgent = $priorityIds['urgent'] ?? $priorityIds->first();

        // Tickets DEMO (sem code — será gerado pelo Model)
        Ticket::create([
            'area_id'             => DB::connection('pgsql')->table('areas')->where('name', 'Development')->value('id'),
            'ticket_type_id'      => DB::connection('pgsql')->table('ticket_types')->where('name', 'Bug Fix')->value('id'),
            'category_id'         => DB::connection('pgsql')->table('categories')->where('name', 'UI/UX')->value('id'),
            'subcategory_id'      => DB::connection('pgsql')->table('subcategories')->where('name', 'Frontend Bug')->value('id'),
            'workflow_id'         => $workflowId,
            'step_id'             => $stepId,
            'priority_id'         => $priorityMedium,
            'title'               => 'Corrigir botão de login quebrado',
            'description'         => 'O botão de login não responde no navegador Firefox.',
            'status'              => 'open',
            'requester_sicode_id' => '11111111-1111-1111-1111-111111111111',
            'sla_due_at'          => (clone $now)->addHours(24),
            'is_late'             => false,
        ]);

        Ticket::create([
            'area_id'             => DB::connection('pgsql')->table('areas')->where('name', 'IT Support')->value('id'),
            'ticket_type_id'      => DB::connection('pgsql')->table('ticket_types')->where('name', 'Incident')->value('id'),
            'priority_id'         => $priorityUrgent,
            'title'               => 'Servidor de e-mail fora do ar',
            'description'         => 'Usuários não conseguem enviar nem receber e-mails.',
            'status'              => 'in_progress',
            'requester_sicode_id' => '22222222-2222-2222-2222-222222222222',
            'manager_sicode_id'   => '33333333-3333-3333-3333-333333333333',
            'executor_sicode_id'  => '44444444-4444-4444-4444-444444444444',
            'sla_due_at'          => (clone $now)->addHours(4),
            'is_late'             => false,
        ]);
    }
}
