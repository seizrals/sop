<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = [
            ['code' => 'sosial', 'name' => 'sosial', 'display_name' => 'Sosial', 'description' => 'Tim statistik sosial'],
            ['code' => 'neraca', 'name' => 'neraca', 'display_name' => 'Neraca', 'description' => 'Tim neraca wilayah dan analisis'],
            ['code' => 'ipds', 'name' => 'ipds', 'display_name' => 'IPDS', 'description' => 'Tim integrasi pengolahan dan diseminasi statistik'],
            ['code' => 'produksi', 'name' => 'produksi', 'display_name' => 'Produksi', 'description' => 'Tim statistik produksi'],
            ['code' => 'umum', 'name' => 'umum', 'display_name' => 'Umum', 'description' => 'Tim umum dan administrasi'],
        ];

        foreach ($teams as $team) {
            Team::updateOrCreate(
                ['code' => $team['code']],
                $team + ['leader_name' => null, 'is_active' => true]
            );
        }
    }
}
