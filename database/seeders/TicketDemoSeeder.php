<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Area;
use App\Models\TicketType;
use App\Models\Category;
use App\Models\Subcategory;

class TicketDemoSeeder extends Seeder
{
    /**
     * Sete TRUE para reset total (local/dev). Em produção: FALSE.
     */
    private const RESET = true;

    public function run(): void
    {
        // Tudo em transação no Postgres
        DB::connection('pgsql')->transaction(function () {

            if (self::RESET) {
                // Zera tabelas em ordem segura (considera FKs) + counters
                DB::statement('TRUNCATE TABLE area_ticket_counters RESTART IDENTITY CASCADE');
                DB::statement('TRUNCATE TABLE subcategories RESTART IDENTITY CASCADE');
                DB::statement('TRUNCATE TABLE categories RESTART IDENTITY CASCADE');
                DB::statement('TRUNCATE TABLE ticket_types RESTART IDENTITY CASCADE');
                DB::statement('TRUNCATE TABLE areas RESTART IDENTITY CASCADE');
            }

            // ========== ÁREAS (com SIGLA) ==========
            $areas = [
                // sigla única + name
                ['sigla' => 'ITS', 'name' => 'IT Support',       'active' => true],
                ['sigla' => 'DEV', 'name' => 'Development',      'active' => true],
                ['sigla' => 'INF', 'name' => 'Infrastructure',   'active' => true],
                // exemplos extras, se quiser:
                // ['sigla' => 'CIP', 'name' => 'Centro Integrado de Projetos', 'active' => true],
                // ['sigla' => 'CST', 'name' => 'Construção',                   'active' => true],
            ];

            foreach ($areas as $a) {
                Area::updateOrCreate(
                    ['sigla' => strtoupper($a['sigla'])],
                    ['name'  => $a['name'], 'active' => $a['active']]
                );
            }

            // Helper para buscar id de área por sigla
            $areaId = fn (string $sigla) => Area::where('sigla', strtoupper($sigla))->value('id');

            // ========== TIPOS DE TICKET ==========
            $typesMap = [
                'DEV' => ['Bug Fix', 'New Feature'],
                'ITS' => ['Incident', 'Request Access'],
                'INF' => ['Server Maintenance'],
            ];

            foreach ($typesMap as $sigla => $names) {
                $aid = $areaId($sigla);
                if (!$aid) {
                    continue;
                }

                foreach ($names as $name) {
                    TicketType::updateOrCreate(
                        ['area_id' => $aid, 'name' => $name],
                        ['active'  => true]
                    );
                }
            }

            // ========== CATEGORIAS ==========
            $categoriesMap = [
                'DEV' => ['UI/UX', 'Backend'],
                'INF' => ['Database', 'Networking'],
            ];

            foreach ($categoriesMap as $sigla => $cats) {
                $aid = $areaId($sigla);
                if (!$aid) {
                    continue;
                }

                foreach ($cats as $name) {
                    Category::updateOrCreate(
                        ['area_id' => $aid, 'name' => $name],
                        ['active'  => true]
                    );
                }
            }

            // Helper para pegar id da categoria por (area, nome)
            $categoryId = function (string $areaSigla, string $catName) {
                $aid = Area::where('sigla', strtoupper($areaSigla))->value('id');
                if (!$aid) {
                    return null;
                }
                return Category::where('area_id', $aid)->where('name', $catName)->value('id');
            };

            // ========== SUBCATEGORIAS ==========
            $subcategoriesMap = [
                // area => [ categoria => [subcats...] ]
                'DEV' => [
                    'UI/UX'   => ['Frontend Bug', 'Design Change'],
                    'Backend' => ['API Endpoint'],
                ],
                'INF' => [
                    'Database'  => ['Database Index'],
                    'Networking' => ['Firewall'],
                ],
            ];

            foreach ($subcategoriesMap as $sigla => $byCat) {
                foreach ($byCat as $catName => $subs) {
                    $cid = $categoryId($sigla, $catName);
                    if (!$cid) {
                        continue;
                    }

                    foreach ($subs as $name) {
                        Subcategory::updateOrCreate(
                            ['category_id' => $cid, 'name' => $name],
                            ['active'      => true]
                        );
                    }
                }
            }
        });
    }
}
