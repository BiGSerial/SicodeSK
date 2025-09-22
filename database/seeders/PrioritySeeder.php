<?php

namespace Database\Seeders;

use App\Models\Priority;
use Illuminate\Database\Seeder;

class PrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priorities = [
            ['name' => 'Urgente', 'slug' => 'urgent', 'weight' => 100, 'color' => '#f97316', 'is_default' => false, 'metadata' => ['base_minutes' => 4 * 60]],
            ['name' => 'Alta', 'slug' => 'high', 'weight' => 80, 'color' => '#facc15', 'is_default' => false, 'metadata' => ['base_minutes' => 8 * 60]],
            ['name' => 'Média', 'slug' => 'medium', 'weight' => 60, 'color' => '#22d3ee', 'is_default' => true, 'metadata' => ['base_minutes' => 16 * 60]],
            ['name' => 'Baixa', 'slug' => 'low', 'weight' => 30, 'color' => '#22c55e', 'is_default' => false, 'metadata' => ['base_minutes' => 24 * 60]],
        ];

        foreach ($priorities as $priority) {
            Priority::query()->updateOrCreate(
                ['slug' => $priority['slug']],
                $priority
            );
        }
    }
}
