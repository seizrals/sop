<?php

namespace Database\Seeders;

use App\Models\SopDocument;
use App\Models\SopTemplate;
use App\Models\Team;
use App\Models\TeamActivity;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TeamSeeder::class,
            UserSeeder::class,
        ]);

        $adminId = User::where('role', 'admin')->value('id');
        $kepala = User::where('role', 'kepala')->first();
        $produksi = Team::where('code', 'produksi')->first();
        $social = Team::where('code', 'sosial')->first();

        if ($produksi) {
            $activity = TeamActivity::updateOrCreate(
                ['team_id' => $produksi->id, 'name' => 'Statistik Air Bersih Tahunan'],
                [
                    'description' => 'Kegiatan utama penyusunan SOP statistik air bersih tahunan.',
                    'is_active' => true,
                ]
            );

            $document = SopDocument::updateOrCreate(
                ['team_id' => $produksi->id, 'team_activity_id' => $activity->id, 'title' => 'SOP Pelaksanaan Kegiatan Statistik Air Bersih Tahunan'],
                [
                    'created_by_id' => $adminId,
                    'updated_by_id' => $adminId,
                    'sop_number' => 'B-59/1902/VS.300/SOP/' . now()->year,
                    'year' => now()->year,
                    'revision_number' => 0,
                    'status' => 'final',
                    'creation_date' => now()->startOfYear()->format('Y-m-d'),
                    'revision_date' => now()->subMonth()->format('Y-m-d'),
                    'effective_date' => now()->format('Y-m-d'),
                    'approval_position' => $kepala?->position,
                    'approval_name' => $kepala?->name,
                    'approval_nip' => $kepala?->nip,
                    'legal_basis' => [
                        'Peraturan Presiden No. 86 Tahun 2007 tentang Badan Pusat Statistik.',
                        'Peraturan Kepala BPS tentang pedoman penyusunan standar operasional prosedur.',
                    ],
                    'executor_qualifications' => [
                        'Memahami alur survei statistik.',
                        'Mampu mengoperasikan aplikasi pengolahan data.',
                    ],
                    'related_documents' => [
                        'SOP pemeriksaan hasil pendataan.',
                    ],
                    'equipment' => [
                        'Laptop',
                        'E-form',
                        'Daftar sampel',
                    ],
                    'warnings' => [
                        'Pastikan seluruh dokumen pendukung telah lengkap sebelum approval.',
                    ],
                    'recording' => [
                        'Seluruh hasil pengolahan tersimpan di server dan arsip kegiatan.',
                    ],
                    'executors' => [
                        ['key' => 'bps_kab_kota', 'label' => 'BPS Kab/Kota'],
                        ['key' => 'ketua_tim', 'label' => 'Ketua Tim'],
                        ['key' => 'pms', 'label' => 'PMS'],
                        ['key' => 'pcs_mitra', 'label' => 'PCS (mitra)'],
                        ['key' => 'operator', 'label' => 'Operator'],
                    ],
                    'activities' => [
                        [
                            'name' => 'Menerima surat, e-form, alokasi, dan daftar sampel.',
                            'performers' => [
                                'bps_kab_kota' => ['type' => 'start', 'label' => 'Start'],
                                'ketua_tim' => ['type' => '', 'label' => ''],
                                'pms' => ['type' => '', 'label' => ''],
                                'pcs_mitra' => ['type' => '', 'label' => ''],
                                'operator' => ['type' => 'process', 'label' => 'Cek'],
                            ],
                            'quality_requirements' => ['Surat', 'E-form', 'Daftar sampel'],
                            'duration' => '2 Minggu',
                            'outputs' => ['Disposisi', 'Alokasi sampel'],
                            'notes' => 'Dokumen masuk diverifikasi terlebih dahulu.',
                        ],
                        [
                            'name' => 'Melakukan pendataan dan approval dokumen.',
                            'performers' => [
                                'bps_kab_kota' => ['type' => '', 'label' => ''],
                                'ketua_tim' => ['type' => 'decision', 'label' => 'Sesuai?'],
                                'pms' => ['type' => 'process', 'label' => 'Input'],
                                'pcs_mitra' => ['type' => 'process', 'label' => 'Isi'],
                                'operator' => ['type' => 'end', 'label' => 'End'],
                            ],
                            'quality_requirements' => ['E-form', 'Daftar sampel'],
                            'duration' => '4 Bulan',
                            'outputs' => ['E-form final', 'Dokumen hasil survei'],
                            'notes' => 'Jika belum sesuai maka dikembalikan untuk revisi.',
                        ],
                    ],
                    'notes' => 'Dokumen contoh hasil seeder.',
                ]
            );

            if (! $document->root_document_id) {
                $document->update(['root_document_id' => $document->id]);
            }

            SopTemplate::updateOrCreate(
                ['name' => 'Template SOP Statistik Air Bersih'],
                [
                    'team_id' => $produksi->id,
                    'team_activity_id' => $activity->id,
                    'source_sop_id' => $document->id,
                    'template_code' => 'TPL-AIR-BERSIH',
                    'description' => 'Template dasar SOP statistik air bersih tahunan.',
                    'template_payload' => [
                        'sop_number' => $document->sop_number,
                        'title' => $document->title,
                        'year' => $document->year,
                        'creation_date' => optional($document->creation_date)->format('Y-m-d'),
                        'revision_date' => optional($document->revision_date)->format('Y-m-d'),
                        'effective_date' => optional($document->effective_date)->format('Y-m-d'),
                        'approval_position' => $document->approval_position,
                        'approval_name' => $document->approval_name,
                        'approval_nip' => $document->approval_nip,
                        'legal_basis' => $document->legal_basis,
                        'executor_qualifications' => $document->executor_qualifications,
                        'related_documents' => $document->related_documents,
                        'equipment' => $document->equipment,
                        'warnings' => $document->warnings,
                        'recording' => $document->recording,
                        'executors' => $document->executors,
                        'activities' => $document->activities,
                        'notes' => $document->notes,
                    ],
                ]
            );
        }

        if ($social) {
            TeamActivity::updateOrCreate(
                ['team_id' => $social->id, 'name' => 'Sakernas'],
                [
                    'description' => 'Kegiatan survei ketenagakerjaan nasional.',
                    'is_active' => true,
                ]
            );
        }
    }
}
