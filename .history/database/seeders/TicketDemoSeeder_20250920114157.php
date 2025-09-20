<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Reset demo tables (somente para teste local!)
        DB::connection('pgsql')->table('subcategories')->truncate();
        DB::connection('pgsql')->table('categories')->truncate();
        DB::connection('pgsql')->table('ticket_types')->truncate();
        DB::connection('pgsql')->table('areas')->truncate();

        // === AREAS ===
        $areas = [
            ['id' => 1, 'name' => 'IT Support', 'active' => true],
            ['id' => 2, 'name' => 'Development', 'active' => true],
            ['id' => 3, 'name' => 'Infrastructure', 'active' => true],
        ];
        DB::connection('pgsql')->table('areas')->insert($areas);

        // === TICKET TYPES ===
        $types = [
            ['id' => 1, 'name' => 'Bug Fix', 'area_id' => 2, 'active' => true],
            ['id' => 2, 'name' => 'New Feature', 'area_id' => 2, 'active' => true],
            ['id' => 3, 'name' => 'Incident', 'area_id' => 1, 'active' => true],
            ['id' => 4, 'name' => 'Request Access', 'area_id' => 1, 'active' => true],
            ['id' => 5, 'name' => 'Server Maintenance', 'area_id' => 3, 'active' => true],
        ];
        DB::connection('pgsql')->table('ticket_types')->insert($types);

        // === CATEGORIES ===
        $categories = [
            ['id' => 1, 'name' => 'UI/UX', 'area_id' => 2, 'active' => true],
            ['id' => 2, 'name' => 'Backend', 'area_id' => 2, 'active' => true],
            ['id' => 3, 'name' => 'Database', 'area_id' => 3, 'active' => true],
            ['id' => 4, 'name' => 'Networking', 'area_id' => 3, 'active' => true],
        ];
        DB::connection('pgsql')->table('categories')->insert($categories);

        // === SUBCATEGORIES ===
        $subcategories = [
            ['id' => 1, 'name' => 'Frontend Bug', 'category_id' => 1, 'active' => true],
            ['id' => 2, 'name' => 'Design Change', 'category_id' => 1, 'active' => true],
            ['id' => 3, 'name' => 'API Endpoint', 'category_id' => 2, 'active' => true],
            ['id' => 4, 'name' => 'Database Index', 'category_id' => 3, 'active' => true],
            ['id' => 5, 'name' => 'Firewall', 'category_id' => 4, 'active' => true],
        ];
        DB::connection('pgsql')->table('subcategories')->insert($subcategories);
    }
}
