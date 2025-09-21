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
            ['name' => 'Urgente', 'slug' => 'urgent', 'weight' => 100, 'color' => '#f97316', 'is_default' => false],
            ['name' => 'Alta', 'slug' => 'high', 'weight' => 80, 'color' => '#facc15', 'is_default' => false],
            ['name' => 'Média', 'slug' => 'medium', 'weight' => 60, 'color' => '#22d3ee', 'is_default' => true],
            ['name' => 'Baixa', 'slug' => 'low', 'weight' => 30, 'color' => '#22c55e', 'is_default' => false],
        ];

        foreach ($priorities as $priority) {
            Priority::query()->updateOrCreate(
                ['slug' => $priority['slug']],
                $priority
            );
        }
    }
}
