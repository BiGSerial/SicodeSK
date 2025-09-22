<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['slug' => 'requester', 'name' => 'Usuário comum', 'description' => 'Pode abrir tickets e acompanhar os seus.'],
            ['slug' => 'agent', 'name' => 'Agente de atendimento', 'description' => 'Trabalha nos tickets da área e comenta internamente.'],
            ['slug' => 'area_manager', 'name' => 'Gestor de área', 'description' => 'Gerencia os tickets da área, aprova fluxos e reatribui.'],
            ['slug' => 'global_manager', 'name' => 'Gestor geral', 'description' => 'Visão consolidada de todas as áreas e dashboards executivos.'],
            ['slug' => 'admin', 'name' => 'Administrador', 'description' => 'Configura o sistema e possui acesso irrestrito.'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                ['name' => $role['name'], 'description' => $role['description']]
            );
        }
    }
}
