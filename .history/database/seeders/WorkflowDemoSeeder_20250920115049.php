<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowDemoSeeder extends Seeder
{
    public function run(): void
    {
        // pega uma área (ex.: Development id=2 do TicketDemoSeeder)
        $areaId = DB::connection('pgsql')->table('areas')->where('name', 'Development')->value('id') ?? 1;

        $workflowId = DB::connection('pgsql')->table('workflows')->insertGetId([
            'area_id' => $areaId,
            'name'    => 'Default Dev Flow',
            'active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection('pgsql')->table('workflow_steps')->insert([
            [
                'workflow_id' => $workflowId,
                'order' => 1,
                'name' => 'Triaging',
                'assign_rule' => 'manager',
                'sla_target_minutes' => 240,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'workflow_id' => $workflowId,
                'order' => 2,
                'name' => 'In Progress',
                'assign_rule' => 'manual',
                'sla_target_minutes' => 1440,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'workflow_id' => $workflowId,
                'order' => 3,
                'name' => 'Review',
                'assign_rule' => 'manager',
                'sla_target_minutes' => 480,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'workflow_id' => $workflowId,
                'order' => 4,
                'name' => 'Done',
                'assign_rule' => 'manual',
                'sla_target_minutes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
