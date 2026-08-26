<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $social = Team::where('code', 'sosial')->first();
        $produksi = Team::where('code', 'produksi')->first();
        $ipds = Team::where('code', 'ipds')->first();

        $users = [
            [
                'name' => 'Admin SOP',
                'email' => 'admin@sop-bps.local',
                'nip' => '197805052000122001',
                'position' => 'Administrator Sistem',
                'role' => 'admin',
                'team_id' => null,
            ],
            [
                'name' => 'Ketua Tim Produksi',
                'email' => 'ketua.produksi@sop-bps.local',
                'nip' => '198102122005011002',
                'position' => 'Ketua Tim Statistik Produksi',
                'role' => 'ketua_tim',
                'team_id' => $produksi?->id,
            ],
            [
                'name' => 'Anggota Tim Social',
                'email' => 'anggota.social@sop-bps.local',
                'nip' => '198905172010011001',
                'position' => 'Statistisi Ahli Muda',
                'role' => 'anggota_tim',
                'team_id' => $social?->id,
            ],
            [
                'name' => 'Kepala BPS',
                'email' => 'kepala@sop-bps.local',
                'nip' => '197001011995031001',
                'position' => 'Kepala BPS Kabupaten Gorontalo Utara',
                'role' => 'kepala',
                'team_id' => null,
            ],
            [
                'name' => 'Ketua Tim IPDS',
                'email' => 'ketua.ipds@sop-bps.local',
                'nip' => '198611112009011003',
                'position' => 'Ketua Tim IPDS',
                'role' => 'ketua_tim',
                'team_id' => $ipds?->id,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user + [
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                ]
            );
        }
    }
}
