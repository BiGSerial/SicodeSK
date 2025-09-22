<?php

namespace Database\Seeders;

use App\Models\SlaRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SlaRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priorityIds = DB::connection('pgsql')->table('priorities')->pluck('id', 'slug');
        $areaIds = DB::connection('pgsql')->table('areas')->pluck('id', 'sigla');

        if ($priorityIds->isEmpty() || $areaIds->isEmpty()) {
            return;
        }

        $rules = [
            [
                'priority_id' => $priorityIds['urgent'] ?? $priorityIds->first(),
                'area_id' => null,
                'ticket_type_id' => null,
                'category_id' => null,
                'subcategory_id' => null,
                'increment_minutes' => 0,
                'tolerance_minutes' => 0,
                'pause_suspends' => false,
                'notes' => 'Regra base urgente',
            ],
            [
                'priority_id' => $priorityIds['high'] ?? $priorityIds->first(),
                'area_id' => $areaIds['ITS'] ?? null,
                'ticket_type_id' => DB::connection('pgsql')->table('ticket_types')->where('name', 'Incident')->value('id'),
                'category_id' => null,
                'subcategory_id' => null,
                'increment_minutes' => 120,
                'tolerance_minutes' => 30,
                'pause_suspends' => true,
                'notes' => 'Incidentes de suporte ganham 2h extras',
            ],
            [
                'priority_id' => $priorityIds['medium'] ?? $priorityIds->first(),
                'area_id' => $areaIds['DEV'] ?? null,
                'ticket_type_id' => DB::connection('pgsql')->table('ticket_types')->where('name', 'Bug Fix')->value('id'),
                'category_id' => DB::connection('pgsql')->table('categories')->where('name', 'UI/UX')->value('id'),
                'subcategory_id' => DB::connection('pgsql')->table('subcategories')->where('name', 'Frontend Bug')->value('id'),
                'increment_minutes' => 90,
                'tolerance_minutes' => 15,
                'pause_suspends' => false,
                'notes' => 'Bugs de UI recebem acréscimo de 1h30',
            ],
        ];

        foreach ($rules as $rule) {
            if (!$rule['priority_id']) {
                continue;
            }

            SlaRule::query()->updateOrCreate(
                [
                    'priority_id' => $rule['priority_id'],
                    'area_id' => $rule['area_id'],
                    'ticket_type_id' => $rule['ticket_type_id'],
                    'category_id' => $rule['category_id'],
                    'subcategory_id' => $rule['subcategory_id'],
                ],
                $rule
            );
        }
    }
}
