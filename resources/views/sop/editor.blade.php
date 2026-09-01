@extends('layouts.app')

@php
    $isEdit = filled($document->id);
    $logoBps = \Illuminate\Support\Facades\Vite::asset('resources/img/logo-bps.png');
    $statusLabel = [
        'draft' => 'Draft',
        'revisi' => 'Revisi',
        'final' => 'Final',
    ];
    $statusClass = [
        'draft' => 'border-slate-200 bg-slate-50 text-slate-700',
        'revisi' => 'border-amber-200 bg-amber-50 text-amber-700',
        'final' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
    ];
    $templateCollection = $templates->count() ? $templates : collect([
        (object) [
            'id' => 1,
            'name' => 'Template Statistik Air Bersih',
            'template_payload' => [
                'sop_number' => 'B-59/1902/VS.300/SOP/2026',
                'title' => 'SOP Pelaksanaan Statistik Air Bersih Tahunan',
                'year' => 2026,
                'creation_date' => '2026-02-10',
                'revision_date' => '2026-03-11',
                'effective_date' => '2026-03-12',
                'approval_position' => 'Kepala BPS Kabupaten Gorontalo Utara',
                'approval_name' => 'Depit Rudianto, SST, M.Ec.Dev.',
                'approval_nip' => '198606302009121003',
                'legal_basis' => ['Peraturan Presiden No. 86 Tahun 2007 tentang Badan Pusat Statistik.'],
                'executor_qualifications' => ['Memahami alur survei statistik.', 'Mampu menggunakan komputer.'],
                'related_documents' => ['SOP monitoring lapangan.'],
                'equipment' => ['Laptop', 'E-form', 'Daftar sampel'],
                'warnings' => ['Pastikan dokumen pendukung lengkap.'],
                'recording' => ['Simpan hasil akhir pada arsip digital.'],
                'executors' => [
                    ['key' => 'bps_kab_kota', 'label' => 'BPS Kab/Kota'],
                    ['key' => 'ketua_tim', 'label' => 'Ketua Tim'],
                    ['key' => 'pms', 'label' => 'PMS'],
                    ['key' => 'pcs_mitra', 'label' => 'PCS (mitra)'],
                ],
                'activities' => [
                    [
                        'name' => 'Menerima dokumen awal kegiatan dan melakukan pengecekan.',
                        'selected_executor_keys' => ['bps_kab_kota', 'ketua_tim'],
                        'performers' => [
                            'bps_kab_kota' => ['type' => 'start', 'label' => 'Start'],
                            'ketua_tim' => ['type' => 'process', 'label' => 'Cek'],
                        ],
                        'quality_requirements' => ['Surat tugas', 'Dokumen pendukung'],
                        'duration' => '1 Hari',
                        'outputs' => ['Dokumen terverifikasi'],
                        'notes' => '',
                    ],
                ],
                'notes' => '',
            ],
        ],
    ]);

    $templatesForJs = $templateCollection->map(fn ($template) => [
        'id' => $template->id,
        'name' => $template->name,
        'payload' => $template->template_payload,
    ])->values();
@endphp

@section('content')
    <form
        method="POST"
        action="{{ $isEdit ? route('sop.update', $document) : route('sop.store', [$team, $activity]) }}"
        id="sop-editor-form"
        class="space-y-6"
    >
        @csrf
        @if ($isEdit)
            @method('PATCH')
        @endif

        <input type="hidden" name="status_action" id="status_action" value="{{ $document->status ?: 'draft' }}">
        <input type="hidden" name="executors_json" id="executors_json">
        <input type="hidden" name="activities_json" id="activities_json">

        @if ($isEdit)
            <section class="overflow-hidden rounded-[32px] border border-blue-200 bg-[linear-gradient(180deg,#eff6ff_0%,#ffffff_100%)] p-6 shadow-[0_20px_45px_-30px_rgba(37,99,235,0.45)]">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-blue-700">SOP Yang Sedang Dibuka</p>
                        <h3 class="mt-2 text-xl font-bold text-slate-900">{{ $document->title ?: 'SOP Tanpa Judul' }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Nomor SOP: <span class="font-semibold text-slate-900">{{ $document->sop_number ?: '-' }}</span>
                            | Kegiatan: <span class="font-semibold text-slate-900">{{ $activity->name }}</span>
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $statusClass[$document->status] ?? 'border-slate-200 bg-slate-50 text-slate-700' }}">
                            {{ strtoupper($statusLabel[$document->status] ?? $document->status ?? 'draft') }}
                        </span>
                        @if ($document->revision_number > 0)
                            <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                Revisi ke-{{ $document->revision_number }}
                            </span>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-700">{{ $team->display_name }} / {{ $activity->name }}</p>
                <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ $isEdit ? 'Edit SOP' : 'Buat SOP Baru' }}</h3>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">Lengkapi identitas SOP dan uraian kegiatan sesuai format dokumen SOP BPS.</p>
            </div>

            <div class="grid gap-3 xl:min-w-[720px] xl:grid-cols-3">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="template-loader">Load Template</label>
                    <select id="template-loader" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                        <option value="">Pilih template</option>
                        @foreach ($templateCollection as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="replace-search">Replace Text</label>
                    <input id="replace-search" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" placeholder="Teks yang dicari">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="replace-with">Pengganti</label>
                    <div class="flex gap-2">
                        <input id="replace-with" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" placeholder="Teks pengganti">
                        <button class="inline-flex shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100" type="button" id="replace-button">Replace</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 transition hover:-translate-y-0.5 hover:border-blue-300 hover:bg-blue-600 hover:text-white disabled:translate-y-0 disabled:cursor-not-allowed disabled:border-blue-100 disabled:bg-blue-100 disabled:text-blue-500"
                id="preview-download-button"
            >
                <span id="preview-download-label">Unduh PDF</span>
            </button>
            <a href="{{ route('sop.activity', [$team, $activity]) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Kembali ke Daftar SOP</a>
        </div>

        <section class="sticky top-4 z-20 rounded-[32px] border border-white/70 bg-white/90 p-4 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
            <div class="grid gap-3 md:grid-cols-2">
                <button
                    type="button"
                    class="step-button rounded-[24px] bg-slate-900 px-5 py-4 text-left text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition"
                    data-step-target="identity-step"
                >
                    <span class="block text-[11px] uppercase tracking-[0.25em] text-slate-300">Langkah 1</span>
                    <span class="mt-1 block text-lg text-white">Identitas SOP</span>
                </button>
                <button
                    type="button"
                    class="step-button rounded-[24px] border border-slate-200 bg-slate-50 px-5 py-4 text-left text-sm font-semibold text-slate-600 transition hover:bg-slate-100"
                    data-step-target="activity-step"
                >
                    <span class="block text-[11px] uppercase tracking-[0.25em] text-slate-400">Langkah 2</span>
                    <span class="mt-1 block text-lg text-slate-800">Uraian Kegiatan</span>
                </button>
            </div>
        </section>

        <div id="identity-step" class="step-panel space-y-6">
            <div class="overflow-hidden rounded-[32px] border border-white/70 bg-[#efefef] p-6 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
                <div class="overflow-hidden border border-black bg-white">
                    <div class="flex flex-col lg:flex-row">
                        <div class="flex min-h-[294px] w-full flex-col items-center justify-center border-b border-black px-6 py-8 text-center lg:w-[58%] lg:border-b-0 lg:border-r">
                            @if ($logoBps)
                                <img src="{{ $logoBps }}" alt="Logo BPS" class="mb-4 h-20 w-auto">
                            @endif
                            <h3 class="text-[17px] font-black uppercase leading-tight text-slate-900">Badan Pusat Statistik</h3>
                            <h3 class="mt-1 text-[17px] font-black uppercase leading-tight text-slate-900">Kabupaten Gorontalo Utara</h3>
                            <h3 class="mt-1 text-[17px] font-black uppercase leading-tight text-slate-900">Tim Statistik {{ strtoupper($team->display_name) }}</h3>
                        </div>

                        <div class="w-full lg:w-[42%]">
                            <div class="grid grid-cols-[34%_66%] border-b border-black">
                                <div class="border-r border-black bg-[#efefef] px-3 py-3 text-[12px] font-black uppercase">Nomor SOP</div>
                                <div class="px-2 py-1.5">
                                    <input name="sop_number" value="{{ old('sop_number', $document->sop_number) }}" class="h-8 w-full border border-slate-300 px-2 text-[12px] outline-none">
                                </div>
                            </div>
                            <div class="grid grid-cols-[34%_66%] border-b border-black">
                                <div class="border-r border-black bg-[#efefef] px-3 py-3 text-[12px] font-black uppercase">Tgl. Pembuatan</div>
                                <div class="px-2 py-1.5">
                                    <input type="date" name="creation_date" value="{{ old('creation_date', optional($document->creation_date)->format('Y-m-d')) }}" class="h-8 w-full border border-slate-300 px-2 text-[12px] outline-none">
                                </div>
                            </div>
                            <div class="grid grid-cols-[34%_66%] border-b border-black">
                                <div class="border-r border-black bg-[#efefef] px-3 py-3 text-[12px] font-black uppercase">Tgl. Revisi</div>
                                <div class="px-2 py-1.5">
                                    <input type="date" name="revision_date" value="{{ old('revision_date', optional($document->revision_date)->format('Y-m-d')) }}" class="h-8 w-full border border-slate-300 px-2 text-[12px] outline-none">
                                </div>
                            </div>
                            <div class="grid grid-cols-[34%_66%] border-b border-black">
                                <div class="border-r border-black bg-[#efefef] px-3 py-3 text-[12px] font-black uppercase">Tgl. Efektif</div>
                                <div class="px-2 py-1.5">
                                    <input type="date" name="effective_date" value="{{ old('effective_date', optional($document->effective_date)->format('Y-m-d')) }}" class="h-8 w-full border border-slate-300 px-2 text-[12px] outline-none">
                                </div>
                            </div>
                            <div class="grid min-h-[132px] grid-cols-[34%_66%] border-b border-black">
                                <div class="border-r border-black bg-[#efefef] px-3 py-3 text-[12px] font-black uppercase">Disahkan Oleh</div>
                                <div class="px-2 py-2">
                                    <div class="text-[11px] text-slate-500">Kepala Badan Pusat Statistik Kabupaten Gorontalo Utara</div>
                                    <input name="approval_position" value="{{ old('approval_position', $document->approval_position) }}" class="mt-2 h-8 w-full border border-slate-300 px-2 text-[12px] outline-none" placeholder="Jabatan pengesah">
                                    <div class="h-8"></div>
                                    <input name="approval_name" value="{{ old('approval_name', $document->approval_name) }}" class="mt-1 h-8 w-full border border-slate-300 px-2 text-center text-[12px] font-bold underline outline-none" placeholder="Nama pengesah">
                                    <input name="approval_nip" value="{{ old('approval_nip', $document->approval_nip) }}" class="mt-1 h-8 w-full border border-slate-300 px-2 text-center text-[12px] outline-none" placeholder="NIP">
                                </div>
                            </div>
                            <div class="grid grid-cols-[34%_66%]">
                                <div class="border-r border-black bg-[#efefef] px-3 py-3 text-[12px] font-black uppercase">Nama SOP</div>
                                <div class="px-2 py-1.5">
                                    <input name="title" value="{{ old('title', $document->title) }}" class="h-8 w-full border border-slate-300 px-2 text-[12px] font-bold outline-none" placeholder="Nama SOP">
                                </div>
                            </div>
                    </div>
                    </div>
                </div>

                <div class="grid border-x border-b border-black bg-white lg:grid-cols-2">
                    <div class="border-b border-black p-4 lg:border-b-0 lg:border-r">
                        <p class="text-[12px] font-black uppercase">Dasar Hukum:</p>
                        <textarea name="legal_basis_text" class="hidden">{{ old('legal_basis_text', implode(PHP_EOL, $document->legal_basis ?? [])) }}</textarea>
                        <div class="list-editor mt-3 space-y-2" data-list-editor="legal_basis_text" data-numbered="true"></div>
                        <button type="button" class="mt-2 text-sm font-medium text-blue-600" data-list-add="legal_basis_text">+ Tambah</button>
                    </div>
                    <div class="p-4">
                        <p class="text-[12px] font-black uppercase">Kualifikasi Pelaksana:</p>
                        <textarea name="executor_qualifications_text" class="hidden">{{ old('executor_qualifications_text', implode(PHP_EOL, $document->executor_qualifications ?? [])) }}</textarea>
                        <div class="list-editor mt-3 space-y-2" data-list-editor="executor_qualifications_text"></div>
                        <button type="button" class="mt-2 text-sm font-medium text-blue-600" data-list-add="executor_qualifications_text">+ Tambah</button>
                    </div>
                </div>
                <div class="grid border-x border-b border-black bg-white lg:grid-cols-2">
                    <div class="border-b border-black p-4 lg:border-b-0 lg:border-r">
                        <p class="text-[12px] font-black uppercase">Keterkaitan:</p>
                        <textarea name="related_documents_text" class="hidden">{{ old('related_documents_text', implode(PHP_EOL, $document->related_documents ?? [])) }}</textarea>
                        <div class="list-editor mt-3 space-y-2" data-list-editor="related_documents_text" data-numbered="true"></div>
                        <button type="button" class="mt-2 text-sm font-medium text-blue-600" data-list-add="related_documents_text">+ Tambah</button>
                    </div>
                    <div class="p-4">
                        <p class="text-[12px] font-black uppercase">Peralatan/Perlengkapan:</p>
                        <textarea name="equipment_text" class="hidden">{{ old('equipment_text', implode(PHP_EOL, $document->equipment ?? [])) }}</textarea>
                        <div class="list-editor mt-3 space-y-2" data-list-editor="equipment_text" data-numbered="true"></div>
                        <button type="button" class="mt-2 text-sm font-medium text-blue-600" data-list-add="equipment_text">+ Tambah</button>
                    </div>
                </div>
                <div class="grid border-x border-b border-black bg-white lg:grid-cols-2">
                    <div class="border-b border-black p-4 lg:border-b-0 lg:border-r">
                        <p class="text-[12px] font-black uppercase">Peringatan:</p>
                        <textarea name="warnings_text" class="hidden">{{ old('warnings_text', implode(PHP_EOL, $document->warnings ?? [])) }}</textarea>
                        <div class="list-editor mt-3 space-y-2" data-list-editor="warnings_text" data-numbered="true"></div>
                        <button type="button" class="mt-2 text-sm font-medium text-blue-600" data-list-add="warnings_text">+ Tambah</button>
                    </div>
                    <div class="p-4">
                        <p class="text-[12px] font-black uppercase">Pencatatan dan Pendataan:</p>
                        <textarea name="recording_text" class="hidden">{{ old('recording_text', implode(PHP_EOL, $document->recording ?? [])) }}</textarea>
                        <div class="list-editor mt-3 space-y-2" data-list-editor="recording_text" data-numbered="true"></div>
                        <button type="button" class="mt-2 text-sm font-medium text-blue-600" data-list-add="recording_text">+ Tambah</button>
                    </div>
                </div>

                <div class="border-x border-b border-black bg-white p-4">
                    <div class="grid gap-4 lg:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="year">Tahun Dokumen</label>
                            <input id="year" name="year" value="{{ old('year', $document->year ?: now()->year) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="notes">Catatan</label>
                            <textarea id="notes" name="notes" class="min-h-24 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">{{ old('notes', $document->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="activity-step" class="step-panel hidden space-y-6">
            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_430px]">
                <section class="overflow-hidden rounded-[32px] border border-white/70 bg-white/85 p-6 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
                    <div>
                        <h3 class="mt-2 text-xl font-bold text-slate-900">Uraian Kegiatan</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Susun langkah kegiatan SOP, pilih pelaksana dari daftar yang tersedia, lalu cek hasilnya langsung pada preview di samping.</p>
                    </div>
                    <div class="mt-6 rounded-[28px] border border-amber-200 bg-[linear-gradient(180deg,#fffaf0_0%,#fffbeb_100%)] p-5 shadow-[0_20px_45px_-30px_rgba(217,119,6,0.45)]">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-700">Master Pelaksana SOP</p>
                                    <p class="mt-2 leading-6">Gunakan daftar pelaksana yang sudah tersimpan. Tambahkan pelaksana baru hanya jika nama yang dibutuhkan belum ada di daftar ini.</p>
                                    <p class="mt-1 leading-6">Pelaksana yang disimpan di sini akan masuk ke database dan bisa dipakai lagi pada SOP berikutnya.</p>
                                </div>
                                <div class="mt-4 rounded-2xl border border-amber-200/80 bg-white/80 p-3">
                                    <div class="flex items-center gap-2 overflow-hidden" id="master-executor-chips"></div>
                                </div>
                                <div id="master-executor-feedback" class="mt-3 hidden rounded-2xl px-4 py-3 text-sm font-medium"></div>
                                <div class="mt-4 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto]">
                                    <div class="relative">
                                        <input
                                            id="master-executor-input"
                                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                                            autocomplete="off"
                                            placeholder="Cari pelaksana tersimpan atau ketik nama baru jika belum ada"
                                        >
                                        <div id="master-executor-menu" class="absolute z-20 mt-2 hidden max-h-56 w-full overflow-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-xl"></div>
                                    </div>
                                    <button type="button" class="inline-flex h-[50px] items-center justify-center rounded-2xl border border-amber-300 bg-white px-4 py-3 text-sm font-semibold text-amber-800 transition hover:bg-amber-50" id="add-master-executor-button">Simpan Pelaksana</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="activity-rows" class="mt-6 space-y-5"></div>
                    <div class="mt-6 flex justify-center">
                        <button type="button" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" id="add-activity-row">Tambah Kegiatan</button>
                    </div>
                </section>

                <aside class="xl:block">
                    <section class="sticky top-28 overflow-hidden rounded-[32px] border border-white/70 bg-white/90 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
                        <div class="border-b border-slate-200 px-5 py-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-blue-700">Preview Tabel PDF</p>
                            <h3 class="mt-1 text-lg font-bold text-slate-900">Tabel Uraian Kegiatan</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-500">Preview ini menampilkan tabel keluaran PDF dari isian yang sedang Anda susun.</p>
                        </div>
                        <div id="activity-preview" class="max-h-[72vh] overflow-auto px-5 py-5"></div>
                    </section>
                </aside>
            </div>
        </div>

        <section class="overflow-hidden rounded-[32px] border border-white/70 bg-white/90 p-6 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
            <div class="grid gap-4 xl:grid-cols-[1.4fr_auto] xl:items-center">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-700">Aksi Dokumen</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-900">{{ $document->title ?: 'SOP Tanpa Judul' }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Aksi penutup lebih nyaman diletakkan di bawah agar pengguna menyelesaikan isi SOP lebih dulu, lalu menyimpan atau finalisasi di akhir.</p>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="button" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100" data-submit-status="{{ $document->status === 'revisi' ? 'revisi' : 'draft' }}">
                    {{ $document->status === 'revisi' ? 'Simpan Revisi' : 'Simpan Draft' }}
                </button>
                <button type="button" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" data-submit-status="final">Finalisasi</button>
            </div>
        </section>
    </form>

    <div id="master-executor-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/45 px-4 py-6">
        <div class="w-full max-w-3xl overflow-hidden rounded-[32px] border border-white/70 bg-white shadow-[0_30px_80px_-35px_rgba(15,23,42,0.35)]">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-blue-700">Master Pelaksana SOP</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-900">Daftar Pelaksana Tersimpan</h3>
                </div>
                <button type="button" id="close-master-executor-modal" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-700">✕</button>
            </div>
            <div class="max-h-[70vh] overflow-auto px-6 py-6">
                <div id="master-executor-modal-list" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3"></div>
            </div>
        </div>
    </div>

    @if ($isEdit)
        <section class="mt-6 overflow-hidden rounded-[32px] border border-white/70 bg-white/85 p-6 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Simpan Sebagai Template</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Template dari SOP ini akan masuk ke menu template SOP untuk dipakai ulang.</p>
                </div>
                <form method="POST" action="{{ route('sop.save-template', $document) }}" class="grid gap-3 md:grid-cols-[1fr_1fr_auto]">
                    @csrf
                    <input name="template_name" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" placeholder="Nama template">
                    <input name="description" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" placeholder="Deskripsi template">
                    <button class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" type="submit">Simpan Template</button>
                </form>
            </div>
        </section>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('sop-editor-form');
            const statusInput = document.getElementById('status_action');
            const executorsInput = document.getElementById('executors_json');
            const activitiesInput = document.getElementById('activities_json');
            const activityRows = document.getElementById('activity-rows');
            const activityPreview = document.getElementById('activity-preview');
            const masterExecutorChips = document.getElementById('master-executor-chips');
            const masterExecutorFeedback = document.getElementById('master-executor-feedback');
            const masterExecutorInput = document.getElementById('master-executor-input');
            const masterExecutorMenu = document.getElementById('master-executor-menu');
            const addMasterExecutorButton = document.getElementById('add-master-executor-button');
            const masterExecutorModal = document.getElementById('master-executor-modal');
            const masterExecutorModalList = document.getElementById('master-executor-modal-list');
            const closeMasterExecutorModal = document.getElementById('close-master-executor-modal');
            const templateLoader = document.getElementById('template-loader');
            const replaceButton = document.getElementById('replace-button');
            const replaceSearch = document.getElementById('replace-search');
            const replaceWith = document.getElementById('replace-with');
            const previewDownloadButton = document.getElementById('preview-download-button');
            const previewDownloadLabel = document.getElementById('preview-download-label');
            const templates = @json($templatesForJs);
            const storeExecutorUrl = @json(route('sop.executor.store', $team));
            const previewDownloadUrl = @json(route('sop.preview-download', [$team, $activity]));
            const csrfToken = @json(csrf_token());
            const listFieldNames = ['legal_basis_text', 'executor_qualifications_text', 'related_documents_text', 'equipment_text', 'warnings_text', 'recording_text'];

            const fieldClass = 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100';
            const areaClass = 'min-h-28 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100';
            const labelClass = 'mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500';
            const subtleButtonClass = 'inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50';
            const dangerButtonClass = 'inline-flex items-center justify-center rounded-2xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100';
            const nodeArrowClass = 'flex h-10 w-10 items-center justify-center self-center text-slate-300';
            const stepActive = ['bg-slate-900', 'text-white', 'shadow-lg', 'shadow-slate-900/10'];
            const stepInactive = ['border', 'border-slate-200', 'bg-slate-50', 'text-slate-600', 'hover:bg-slate-100'];

            let executors = @json($editorPayload['executors']);
            let activities = @json($editorPayload['activities']);

            const normalizeExecutorKey = (value) => String(value || '').trim().toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
            const splitLines = (value) => String(value || '').split(/\r?\n/).map((line) => line.trim()).filter(Boolean);
            const splitLinesPreserveEmpty = (value) => String(value || '').split(/\r?\n/).map((line) => line.trim());

            const emptyActivity = () => ({
                name: '',
                flow_nodes: [],
                selected_executor_keys: [],
                performers: {},
                quality_requirements: [],
                duration: '',
                outputs: [],
                notes: '',
            });

            const ensureActivityState = (row) => {
                const legacyNodes = Object.entries(row.performers || {}).map(([executorKey, performer]) => ({
                    executor_key: executorKey,
                    type: performer?.type || '',
                    label: performer?.type === 'decision' ? (performer?.label || '') : '',
                    yes_target: performer?.type === 'decision' ? (performer?.yes_target || '') : '',
                    no_target: performer?.type === 'decision' ? (performer?.no_target || '') : '',
                    yes_target_executor_key: performer?.type === 'decision' ? (performer?.yes_target_executor_key || '') : '',
                    no_target_executor_key: performer?.type === 'decision' ? (performer?.no_target_executor_key || '') : '',
                }));

                row.flow_nodes = Array.isArray(row.flow_nodes) ? row.flow_nodes : legacyNodes;
                row.flow_nodes = row.flow_nodes
                    .map((node) => {
                        const executorKey = normalizeExecutorKey(node.executor_key);
                        const type = String(node.type || '').trim();

                        if (!executorKey || !type || !executors.some((executor) => executor.key === executorKey)) {
                            return null;
                        }

                        return {
                            executor_key: executorKey,
                            type,
                            label: type === 'decision' ? String(node.label || '').trim() : '',
                            yes_target: type === 'decision' && Number(node.yes_target) > 0 ? Number(node.yes_target) : '',
                            no_target: type === 'decision' && Number(node.no_target) > 0 ? Number(node.no_target) : '',
                            yes_target_executor_key: type === 'decision' ? normalizeExecutorKey(node.yes_target_executor_key || '') : '',
                            no_target_executor_key: type === 'decision' ? normalizeExecutorKey(node.no_target_executor_key || '') : '',
                        };
                    })
                    .filter(Boolean);

                row.selected_executor_keys = [...new Set(row.flow_nodes.map((node) => node.executor_key))];
                row.performers = Object.fromEntries(
                    row.selected_executor_keys.map((key) => {
                        const node = row.flow_nodes.find((item) => item.executor_key === key);
                        return [key, {
                            type: node?.type || '',
                            label: node?.type === 'decision' ? (node?.label || '') : '',
                            yes_target: node?.type === 'decision' ? (node?.yes_target || '') : '',
                            no_target: node?.type === 'decision' ? (node?.no_target || '') : '',
                            yes_target_executor_key: node?.type === 'decision' ? (node?.yes_target_executor_key || '') : '',
                            no_target_executor_key: node?.type === 'decision' ? (node?.no_target_executor_key || '') : '',
                        }];
                    })
                );
            };

            const serialize = () => {
                executorsInput.value = JSON.stringify(executors);
                activitiesInput.value = JSON.stringify(activities);
            };

            const filenameFromDisposition = (value) => {
                const match = String(value || '').match(/filename\*?=(?:UTF-8''|")?([^";]+)/i);
                return match ? decodeURIComponent(match[1].replace(/"/g, '')) : null;
            };

            const syncActivitiesFromDom = () => {
                activities = activities.map((row, index) => {
                    const nextRow = {
                        ...row,
                        flow_nodes: Array.isArray(row.flow_nodes) ? row.flow_nodes.map((node) => ({ ...node })) : [],
                    };

                    const rowNameField = activityRows.querySelector(`[data-row-name="${index}"]`);
                    const rowQualityField = activityRows.querySelector(`[data-row-quality="${index}"]`);
                    const rowDurationField = activityRows.querySelector(`[data-row-duration="${index}"]`);
                    const rowOutputField = activityRows.querySelector(`[data-row-output="${index}"]`);
                    const rowNotesField = activityRows.querySelector(`[data-row-notes="${index}"]`);

                    if (rowNameField) {
                        nextRow.name = rowNameField.value;
                    }

                    if (rowQualityField) {
                        nextRow.quality_requirements = splitLines(rowQualityField.value);
                    }

                    if (rowDurationField) {
                        nextRow.duration = rowDurationField.value;
                    }

                    if (rowOutputField) {
                        nextRow.outputs = splitLines(rowOutputField.value);
                    }

                    if (rowNotesField) {
                        nextRow.notes = rowNotesField.value;
                    }

                    nextRow.flow_nodes = nextRow.flow_nodes.map((node, nodeIndex) => {
                        const executorField = activityRows.querySelector(`[data-node-executor="${index}"][data-node-index="${nodeIndex}"]`);
                        const typeField = activityRows.querySelector(`[data-node-type="${index}"][data-node-index="${nodeIndex}"]`);
                        const labelField = activityRows.querySelector(`[data-node-label="${index}"][data-node-index="${nodeIndex}"]`);
                        const yesTargetField = activityRows.querySelector(`[data-node-yes-target="${index}"][data-node-index="${nodeIndex}"]`);
                        const noTargetField = activityRows.querySelector(`[data-node-no-target="${index}"][data-node-index="${nodeIndex}"]`);
                        const yesTargetExecutorField = activityRows.querySelector(`[data-node-yes-target-executor="${index}"][data-node-index="${nodeIndex}"]`);
                        const noTargetExecutorField = activityRows.querySelector(`[data-node-no-target-executor="${index}"][data-node-index="${nodeIndex}"]`);
                        const nextType = typeField ? String(typeField.value || '').trim() : String(node.type || '').trim();

                        return {
                            ...node,
                            executor_key: executorField ? executorField.value : node.executor_key,
                            type: nextType,
                            label: nextType === 'decision' ? String(labelField?.value || '').trim() : '',
                            yes_target: nextType === 'decision' && yesTargetField?.value ? Number(yesTargetField.value) : '',
                            no_target: nextType === 'decision' && noTargetField?.value ? Number(noTargetField.value) : '',
                            yes_target_executor_key: nextType === 'decision' ? normalizeExecutorKey(yesTargetExecutorField?.value || '') : '',
                            no_target_executor_key: nextType === 'decision' ? normalizeExecutorKey(noTargetExecutorField?.value || '') : '',
                        };
                    });

                    return nextRow;
                });
            };

            const syncFormStateBeforeSubmit = () => {
                listFieldNames.forEach((fieldName) => syncListField(fieldName));
                syncActivitiesFromDom();
                activities.forEach((row) => ensureActivityState(row));
                serialize();
            };

            const syncListField = (fieldName) => {
                const hiddenField = document.querySelector(`[name="${fieldName}"]`);
                const container = document.querySelector(`[data-list-editor="${fieldName}"]`);

                if (!hiddenField || !container) {
                    return;
                }

                hiddenField.value = [...container.querySelectorAll('[data-list-item-input]')]
                    .map((input) => input.value.trim())
                    .filter(Boolean)
                    .join('\n');
            };

            const renderListEditor = (fieldName) => {
                const hiddenField = document.querySelector(`[name="${fieldName}"]`);
                const container = document.querySelector(`[data-list-editor="${fieldName}"]`);

                if (!hiddenField || !container) {
                    return;
                }

                const numbered = container.dataset.numbered === 'true';
                const values = splitLinesPreserveEmpty(hiddenField.value);
                const rows = values.length ? values : [''];

                container.innerHTML = rows.map((value, index) => `
                    <div class="flex items-start gap-2">
                        ${numbered ? `<div class="w-5 pt-2 text-right text-[12px] text-slate-500">${index + 1}.</div>` : ''}
                        <input type="text" value="${value.replace(/"/g, '&quot;')}" class="h-8 w-full border border-slate-300 px-2 text-[12px] outline-none" data-list-item-input data-list-field="${fieldName}">
                        <button type="button" class="h-8 w-8 shrink-0 text-red-500" data-remove-list-item="${fieldName}" data-remove-index="${index}">x</button>
                    </div>
                `).join('');

                container.querySelectorAll('[data-list-item-input]').forEach((input) => {
                    input.addEventListener('input', () => syncListField(fieldName));
                });

                container.querySelectorAll('[data-remove-list-item]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const nextValues = splitLinesPreserveEmpty(hiddenField.value);
                        nextValues.splice(Number(button.dataset.removeIndex), 1);
                        hiddenField.value = nextValues.join('\n');
                        renderListEditor(fieldName);
                    });
                });
            };

            const renderListEditors = () => {
                listFieldNames.forEach((fieldName) => renderListEditor(fieldName));
            };

            const escapeHtml = (value) => String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const executorSearchResultsMarkup = (query = '') => {
                const keyword = query.trim().toLowerCase();
                const filtered = executors.filter((executor) => executor.label.toLowerCase().includes(keyword));

                return filtered.length
                    ? filtered.map((executor) => `
                        <button
                            type="button"
                            class="flex w-full items-center rounded-xl px-3 py-2 text-left text-sm text-slate-600 transition hover:bg-blue-50 hover:text-blue-700"
                            data-executor-value="${escapeHtml(executor.label)}"
                        >
                            ${escapeHtml(executor.label)}
                        </button>
                    `).join('')
                    : '<div class="rounded-xl px-3 py-2 text-sm text-slate-400">Tidak ada pelaksana yang cocok.</div>';
            };

            const renderMasterExecutorPanel = () => {
                if (!masterExecutorChips) {
                    return;
                }

                masterExecutorChips.innerHTML = executors.length
                    ? `
                        <div class="flex min-w-0 flex-1 items-center gap-2 overflow-hidden">
                            ${executors.slice(0, 4).map((executor) => `<div class="truncate rounded-full border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700" title="${escapeHtml(executor.label)}">${escapeHtml(executor.label)}</div>`).join('')}
                            ${executors.length > 4 ? `<button type="button" id="open-master-executor-modal" class="shrink-0 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Lihat lainnya (${executors.length - 4})</button>` : ''}
                        </div>
                    `
                    : '<span class="text-sm text-slate-500">Belum ada pelaksana tersimpan.</span>';

                if (masterExecutorModalList) {
                    masterExecutorModalList.innerHTML = executors.length
                        ? executors.map((executor) => `<div class="truncate rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700" title="${escapeHtml(executor.label)}">${escapeHtml(executor.label)}</div>`).join('')
                        : '<div class="text-sm text-slate-500">Belum ada pelaksana tersimpan.</div>';
                }

                document.getElementById('open-master-executor-modal')?.addEventListener('click', () => {
                    masterExecutorModal?.classList.remove('hidden');
                    masterExecutorModal?.classList.add('flex');
                });
            };

            const renderMasterExecutorResults = (query = '') => {
                if (!masterExecutorMenu) {
                    return;
                }

                masterExecutorMenu.innerHTML = executorSearchResultsMarkup(query);
            };

            const setMasterExecutorFeedback = (message = '', tone = 'info') => {
                if (!masterExecutorFeedback) {
                    return;
                }

                if (!message) {
                    masterExecutorFeedback.className = 'mt-3 hidden rounded-2xl px-4 py-3 text-sm font-medium';
                    masterExecutorFeedback.textContent = '';
                    return;
                }

                const toneClass = tone === 'success'
                    ? 'mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700'
                    : tone === 'error'
                        ? 'mt-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700'
                        : 'mt-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-700';

                masterExecutorFeedback.className = toneClass;
                masterExecutorFeedback.textContent = message;
            };

            closeMasterExecutorModal?.addEventListener('click', () => {
                masterExecutorModal?.classList.add('hidden');
                masterExecutorModal?.classList.remove('flex');
            });

            masterExecutorModal?.addEventListener('click', (event) => {
                if (event.target === masterExecutorModal) {
                    masterExecutorModal.classList.add('hidden');
                    masterExecutorModal.classList.remove('flex');
                }
            });

            const activityTargetLabel = (target, executorKey = '') => {
                const number = Number(target);
                if (number <= 0) {
                    return '-';
                }

                const activity = activities[number - 1];
                if (!activity) {
                    return `Kegiatan ${number}`;
                }

                const selectedNodeLabel = selectedTargetNodeLabel(target, executorKey);
                if (selectedNodeLabel) {
                    return `Kegiatan ${number} -> ${selectedNodeLabel}`;
                }

                const firstNode = Array.isArray(activity.flow_nodes) ? activity.flow_nodes[0] : null;
                const executorLabel = firstNode
                    ? (executors.find((executor) => executor.key === firstNode.executor_key)?.label || firstNode.executor_key)
                    : null;

                return executorLabel
                    ? `Kegiatan ${number} -> ${executorLabel}`
                    : `Kegiatan ${number}`;
            };

            const activityTargetNodeOptions = (target) => {
                const activity = activities[Number(target) - 1];
                if (!activity || !Array.isArray(activity.flow_nodes)) {
                    return [];
                }

                return activity.flow_nodes
                    .filter((node) => node && node.executor_key)
                    .map((node, nodeIndex) => ({
                        key: normalizeExecutorKey(node.executor_key),
                        label: `Node ${nodeIndex + 1} - ${executors.find((executor) => executor.key === node.executor_key)?.label || node.executor_key}`,
                    }))
                    .filter((option, optionIndex, allOptions) => option.key && allOptions.findIndex((item) => item.key === option.key) === optionIndex);
            };

            const selectedTargetNodeLabel = (target, executorKey) => {
                const options = activityTargetNodeOptions(target);
                const selected = options.find((option) => option.key === normalizeExecutorKey(executorKey));
                return selected ? selected.label : '';
            };

            const shapePreviewMarkup = (type, label = '') => {
                if (type === 'decision') {
                    return `
                        <div class="relative flex h-16 w-16 items-center justify-center">
                            <div class="absolute inset-1 rotate-45 rounded-sm border border-slate-400 bg-white"></div>
                            <span class="relative z-10 max-w-[42px] text-center text-[9px] font-semibold leading-tight text-slate-700">${escapeHtml(label || 'Keputusan')}</span>
                        </div>
                    `;
                }

                if (type === 'start' || type === 'end') {
                    return '<div class="h-8 w-16 rounded-full border border-slate-400 bg-white"></div>';
                }

                if (type === 'process') {
                    return '<div class="h-10 w-20 rounded-sm border border-slate-400 bg-white"></div>';
                }

                return '<div class="h-8 w-16 rounded-sm border border-dashed border-slate-300 bg-slate-50"></div>';
            };

            const renderActivityPreview = () => {
                if (!activityPreview) {
                    return;
                }

                if (!activities.length) {
                    activityPreview.innerHTML = `
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                            Tambahkan kegiatan untuk melihat preview alur dan tabel hasil PDF.
                        </div>
                    `;
                    return;
                }

                const executorHeaders = executors.length
                    ? executors.map((executor) => `<th class="border border-slate-300 bg-slate-50 px-2 py-2 text-center text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">${escapeHtml(executor.label)}</th>`).join('')
                    : '<th class="border border-slate-300 bg-slate-50 px-2 py-2 text-center text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">Pelaksana</th>';

                const bodyRows = activities.map((row, index) => {
                    const firstNodeByExecutor = {};
                    (row.flow_nodes || []).forEach((node) => {
                        if (!firstNodeByExecutor[node.executor_key]) {
                            firstNodeByExecutor[node.executor_key] = node;
                        }
                    });

                    const executorCells = (executors.length ? executors : [{ key: 'executor', label: 'Pelaksana' }]).map((executor) => {
                        const node = firstNodeByExecutor[executor.key];
                        return `
                            <td class="border border-slate-300 px-2 py-2">
                                <div class="flex min-h-[42px] items-center justify-center">
                                    ${node ? `${shapePreviewMarkup(node.type, node.label)}` : ''}
                                </div>
                            </td>
                        `;
                    }).join('');

                    return `
                        <tr>
                            <td class="border border-slate-300 px-2 py-2 text-center text-[11px]">${index + 1}</td>
                            <td class="border border-slate-300 px-2 py-2 text-[11px] leading-5">${escapeHtml(row.name || '-')}</td>
                            ${executorCells}
                            <td class="border border-slate-300 px-2 py-2 text-[11px] leading-5">${(row.quality_requirements || []).map(escapeHtml).join('<br>')}</td>
                            <td class="border border-slate-300 px-2 py-2 text-center text-[11px]">${escapeHtml(row.duration || '-')}</td>
                            <td class="border border-slate-300 px-2 py-2 text-[11px] leading-5">${(row.outputs || []).map(escapeHtml).join('<br>')}</td>
                            <td class="border border-slate-300 px-2 py-2 text-[11px] leading-5">${escapeHtml(row.notes || '-')}</td>
                        </tr>
                    `;
                }).join('');

                activityPreview.innerHTML = `
                    <div>
                        <div class="overflow-auto rounded-2xl border border-slate-200 bg-white">
                            <table class="min-w-[820px] border-collapse text-left">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="border border-slate-300 bg-slate-50 px-2 py-2 text-center text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">No</th>
                                        <th rowspan="2" class="border border-slate-300 bg-slate-50 px-2 py-2 text-center text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">Kegiatan</th>
                                        <th colspan="${Math.max(executors.length, 1)}" class="border border-slate-300 bg-slate-50 px-2 py-2 text-center text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">Pelaksana</th>
                                        <th colspan="3" class="border border-slate-300 bg-slate-50 px-2 py-2 text-center text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">Mutu Baku</th>
                                        <th rowspan="2" class="border border-slate-300 bg-slate-50 px-2 py-2 text-center text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">Keterangan</th>
                                    </tr>
                                    <tr>
                                        ${executorHeaders}
                                        <th class="border border-slate-300 bg-slate-50 px-2 py-2 text-center text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">Kelengkapan</th>
                                        <th class="border border-slate-300 bg-slate-50 px-2 py-2 text-center text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">Waktu</th>
                                        <th class="border border-slate-300 bg-slate-50 px-2 py-2 text-center text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">Output</th>
                                    </tr>
                                </thead>
                                <tbody>${bodyRows}</tbody>
                            </table>
                        </div>
                    </div>
                `;
            };

            const renderActivities = () => {
                activityRows.innerHTML = '';

                activities.forEach((row, index) => {
                    ensureActivityState(row);

                    const flowNodeMarkup = (row.flow_nodes || []).map((node, nodeIndex) => {
                        const executor = executors.find((item) => item.key === node.executor_key);
                        const shapeMarkup = node.type === 'decision'
                            ? `
                                <div class="relative flex h-20 w-20 items-center justify-center self-center">
                                    <div class="absolute inset-2 rotate-45 rounded-sm border border-slate-400 bg-white"></div>
                                    <span class="relative z-10 max-w-[56px] text-center text-[10px] font-semibold leading-tight text-slate-700">${node.label || 'Keputusan'}</span>
                                </div>
                            `
                            : (node.type === 'start' || node.type === 'end')
                                ? '<div class="h-10 w-20 self-center rounded-full border border-slate-400 bg-white"></div>'
                                : '<div class="h-12 w-24 self-center rounded-sm border border-slate-400 bg-white"></div>';

                        const card = `
                            <div class="min-w-[220px] rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-2">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Node ${nodeIndex + 1}</p>
                                    <button type="button" class="inline-flex items-center justify-center rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-red-600 transition hover:bg-red-100" data-remove-node="${index}" data-node-index="${nodeIndex}">Hapus</button>
                                </div>
                                <div class="mt-3 flex flex-col gap-3">
                                    ${shapeMarkup}
                                </div>
                                <div class="mt-3">
                                    <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Pelaksana</label>
                                    <select class="${fieldClass}" data-node-executor="${index}" data-node-index="${nodeIndex}">
                                        <option value="">Pilih pelaksana</option>
                                        ${executors.map((option) => `<option value="${option.key}" ${option.key === node.executor_key ? 'selected' : ''}>${option.label}</option>`).join('')}
                                    </select>
                                </div>
                                <div class="mt-3">
                                    <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Shape</label>
                                    <select class="${fieldClass}" data-node-type="${index}" data-node-index="${nodeIndex}">
                                        <option value="">Kosong</option>
                                        <option value="start" ${node.type === 'start' ? 'selected' : ''}>Start</option>
                                        <option value="process" ${node.type === 'process' ? 'selected' : ''}>Proses</option>
                                        <option value="decision" ${node.type === 'decision' ? 'selected' : ''}>Decision</option>
                                        <option value="end" ${node.type === 'end' ? 'selected' : ''}>End</option>
                                    </select>
                                </div>
                                ${node.type === 'decision'
                                    ? `
                                        <div class="mt-3">
                                            <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Teks Decision</label>
                                            <input class="${fieldClass}" value="${(node.label || '').replace(/"/g, '&quot;')}" data-node-label="${index}" data-node-index="${nodeIndex}" placeholder="Teks di dalam decision">
                                        </div>
                                        <div class="mt-3 grid gap-3">
                                            <div>
                                                <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-600">Jika Ya Ke</label>
                                                <select class="${fieldClass}" data-node-yes-target="${index}" data-node-index="${nodeIndex}">
                                                    <option value="">Pilih kegiatan tujuan</option>
                                                    ${activities.map((_, activityIndex) => `<option value="${activityIndex + 1}" ${Number(node.yes_target) === activityIndex + 1 ? 'selected' : ''}>Kegiatan ${activityIndex + 1}</option>`).join('')}
                                                </select>
                                                ${activityTargetNodeOptions(node.yes_target).length > 1 ? `
                                                    <div class="mt-3">
                                                        <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-600">Node Tujuan Ya</label>
                                                        <select class="${fieldClass}" data-node-yes-target-executor="${index}" data-node-index="${nodeIndex}">
                                                            <option value="">Pilih node tujuan</option>
                                                            ${activityTargetNodeOptions(node.yes_target).map((option) => `<option value="${option.key}" ${normalizeExecutorKey(node.yes_target_executor_key) === option.key ? 'selected' : ''}>${option.label}</option>`).join('')}
                                                        </select>
                                                    </div>
                                                ` : ''}
                                            </div>
                                            <div>
                                                <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.2em] text-rose-600">Jika Tidak Ke</label>
                                                <select class="${fieldClass}" data-node-no-target="${index}" data-node-index="${nodeIndex}">
                                                    <option value="">Pilih kegiatan tujuan</option>
                                                    ${activities.map((_, activityIndex) => `<option value="${activityIndex + 1}" ${Number(node.no_target) === activityIndex + 1 ? 'selected' : ''}>Kegiatan ${activityIndex + 1}</option>`).join('')}
                                                </select>
                                                ${activityTargetNodeOptions(node.no_target).length > 1 ? `
                                                    <div class="mt-3">
                                                        <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.2em] text-rose-600">Node Tujuan Tidak</label>
                                                        <select class="${fieldClass}" data-node-no-target-executor="${index}" data-node-index="${nodeIndex}">
                                                            <option value="">Pilih node tujuan</option>
                                                            ${activityTargetNodeOptions(node.no_target).map((option) => `<option value="${option.key}" ${normalizeExecutorKey(node.no_target_executor_key) === option.key ? 'selected' : ''}>${option.label}</option>`).join('')}
                                                        </select>
                                                    </div>
                                                ` : ''}
                                            </div>
                                        </div>
                                    `
                                    : ''
                                }
                                <div class="mt-3 rounded-2xl bg-slate-50 px-3 py-2 text-xs text-slate-500">${executor ? executor.label : 'Pilih pelaksana untuk node ini.'}</div>
                            </div>
                        `;

                        if (nodeIndex >= row.flow_nodes.length - 1) {
                            return card;
                        }

                        return `${card}<div class="${nodeArrowClass}"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M5 12H19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M13 6L19 12L13 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></div>`;
                    }).join('') || '<div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-500">Belum ada node pada kegiatan ini. Tambahkan node sesuai urutan alur.</div>';

                    const wrapper = document.createElement('div');
                    wrapper.className = 'rounded-[28px] border border-slate-200 bg-[linear-gradient(180deg,#ffffff_0%,#f8fbff_100%)] p-5';
                    wrapper.innerHTML = `
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-700">Langkah ${index + 1}</p>
                                <h4 class="mt-1 text-lg font-semibold text-slate-900">Kegiatan SOP</h4>
                            </div>
                            <button type="button" class="${dangerButtonClass}" data-remove-row="${index}">Hapus</button>
                        </div>
                        <div class="mt-4 grid gap-4">
                            <div>
                                <label class="${labelClass}">Nama Kegiatan</label>
                                <textarea class="${areaClass}" data-row-name="${index}" placeholder="Uraian kegiatan">${row.name || ''}</textarea>
                            </div>
                            <div>
                                <div class="flex items-center justify-between gap-3">
                                    <label class="${labelClass} !mb-0">Alur Node Kegiatan</label>
                                    <button type="button" class="inline-flex h-[44px] items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" data-add-node="${index}">Tambah Node</button>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-3">${flowNodeMarkup}</div>
                            </div>
                            <div class="grid gap-4 xl:grid-cols-4">
                                <div class="xl:col-span-2">
                                    <label class="${labelClass}">Mutu Baku / Kelengkapan</label>
                                    <textarea class="${areaClass} min-h-24" data-row-quality="${index}" placeholder="Satu baris untuk satu kelengkapan">${(row.quality_requirements || []).join('\n')}</textarea>
                                </div>
                                <div>
                                    <label class="${labelClass}">Waktu</label>
                                    <input class="${fieldClass}" value="${row.duration || ''}" data-row-duration="${index}" placeholder="Contoh: 2 Minggu">
                                </div>
                                <div>
                                    <label class="${labelClass}">Output</label>
                                    <textarea class="${areaClass} min-h-24" data-row-output="${index}" placeholder="Satu baris untuk satu output">${(row.outputs || []).join('\n')}</textarea>
                                </div>
                            </div>
                            <div>
                                <label class="${labelClass}">Keterangan</label>
                                <textarea class="${areaClass} min-h-24" data-row-notes="${index}" placeholder="Keterangan tambahan">${row.notes || ''}</textarea>
                            </div>
                        </div>
                    `;

                    activityRows.appendChild(wrapper);
                });

                activityRows.querySelectorAll('[data-remove-row]').forEach((button) => {
                    button.addEventListener('click', () => {
                        activities.splice(Number(button.dataset.removeRow), 1);
                        renderAll();
                    });
                });

                activityRows.querySelectorAll('[data-add-node]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const row = activities[Number(button.dataset.addNode)];
                        if (!executors.length) {
                            return;
                        }

                        row.flow_nodes = Array.isArray(row.flow_nodes) ? row.flow_nodes : [];
                        row.flow_nodes.push({
                            executor_key: executors[0].key,
                            type: 'process',
                            label: '',
                            yes_target: '',
                            no_target: '',
                            yes_target_executor_key: '',
                            no_target_executor_key: '',
                        });
                        renderAll();
                    });
                });

                activityRows.querySelectorAll('[data-remove-node]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const rowIndex = Number(button.dataset.removeNode);
                        const nodeIndex = Number(button.dataset.nodeIndex);
                        activities[rowIndex].flow_nodes.splice(nodeIndex, 1);
                        renderAll();
                    });
                });

                activityRows.querySelectorAll('[data-row-name]').forEach((input) => {
                    input.addEventListener('input', () => {
                        activities[Number(input.dataset.rowName)].name = input.value;
                        serialize();
                    });
                });

                activityRows.querySelectorAll('[data-row-quality]').forEach((input) => {
                    input.addEventListener('input', () => {
                        activities[Number(input.dataset.rowQuality)].quality_requirements = splitLines(input.value);
                        serialize();
                    });
                });

                activityRows.querySelectorAll('[data-row-duration]').forEach((input) => {
                    input.addEventListener('input', () => {
                        activities[Number(input.dataset.rowDuration)].duration = input.value;
                        serialize();
                    });
                });

                activityRows.querySelectorAll('[data-row-output]').forEach((input) => {
                    input.addEventListener('input', () => {
                        activities[Number(input.dataset.rowOutput)].outputs = splitLines(input.value);
                        serialize();
                    });
                });

                activityRows.querySelectorAll('[data-row-notes]').forEach((input) => {
                    input.addEventListener('input', () => {
                        activities[Number(input.dataset.rowNotes)].notes = input.value;
                        serialize();
                    });
                });

                activityRows.querySelectorAll('[data-node-executor]').forEach((input) => {
                    input.addEventListener('change', () => {
                        const rowIndex = Number(input.dataset.nodeExecutor);
                        const nodeIndex = Number(input.dataset.nodeIndex);
                        activities[rowIndex].flow_nodes[nodeIndex].executor_key = input.value;
                        renderAll();
                    });
                });

                activityRows.querySelectorAll('[data-node-type]').forEach((input) => {
                    input.addEventListener('change', () => {
                        const rowIndex = Number(input.dataset.nodeType);
                        const nodeIndex = Number(input.dataset.nodeIndex);
                        activities[rowIndex].flow_nodes[nodeIndex].type = input.value;
                        if (input.value !== 'decision') {
                            activities[rowIndex].flow_nodes[nodeIndex].label = '';
                            activities[rowIndex].flow_nodes[nodeIndex].yes_target = '';
                            activities[rowIndex].flow_nodes[nodeIndex].no_target = '';
                        activities[rowIndex].flow_nodes[nodeIndex].yes_target_executor_key = '';
                        activities[rowIndex].flow_nodes[nodeIndex].no_target_executor_key = '';
                        }
                        renderAll();
                    });
                });

                activityRows.querySelectorAll('[data-node-label]').forEach((input) => {
                    input.addEventListener('input', () => {
                        const rowIndex = Number(input.dataset.nodeLabel);
                        const nodeIndex = Number(input.dataset.nodeIndex);
                        activities[rowIndex].flow_nodes[nodeIndex].label = input.value;
                        serialize();
                    });
                });

                activityRows.querySelectorAll('[data-node-yes-target]').forEach((input) => {
                    input.addEventListener('change', () => {
                        const rowIndex = Number(input.dataset.nodeYesTarget);
                        const nodeIndex = Number(input.dataset.nodeIndex);
                        const node = activities[rowIndex].flow_nodes[nodeIndex];
                        node.yes_target = input.value ? Number(input.value) : '';
                        const options = activityTargetNodeOptions(node.yes_target);
                        if (options.length <= 1 || !options.some((option) => option.key === normalizeExecutorKey(node.yes_target_executor_key))) {
                            node.yes_target_executor_key = '';
                        }
                        renderAll();
                    });
                });

                activityRows.querySelectorAll('[data-node-no-target]').forEach((input) => {
                    input.addEventListener('change', () => {
                        const rowIndex = Number(input.dataset.nodeNoTarget);
                        const nodeIndex = Number(input.dataset.nodeIndex);
                        const node = activities[rowIndex].flow_nodes[nodeIndex];
                        node.no_target = input.value ? Number(input.value) : '';
                        const options = activityTargetNodeOptions(node.no_target);
                        if (options.length <= 1 || !options.some((option) => option.key === normalizeExecutorKey(node.no_target_executor_key))) {
                            node.no_target_executor_key = '';
                        }
                        renderAll();
                    });
                });

                activityRows.querySelectorAll('[data-node-yes-target-executor]').forEach((input) => {
                    input.addEventListener('change', () => {
                        const rowIndex = Number(input.dataset.nodeYesTargetExecutor);
                        const nodeIndex = Number(input.dataset.nodeIndex);
                        activities[rowIndex].flow_nodes[nodeIndex].yes_target_executor_key = normalizeExecutorKey(input.value || '');
                        renderAll();
                    });
                });

                activityRows.querySelectorAll('[data-node-no-target-executor]').forEach((input) => {
                    input.addEventListener('change', () => {
                        const rowIndex = Number(input.dataset.nodeNoTargetExecutor);
                        const nodeIndex = Number(input.dataset.nodeIndex);
                        activities[rowIndex].flow_nodes[nodeIndex].no_target_executor_key = normalizeExecutorKey(input.value || '');
                        renderAll();
                    });
                });
            };

            const renderAll = () => {
                activities.forEach((row) => ensureActivityState(row));
                renderMasterExecutorPanel();
                renderActivities();
                renderActivityPreview();
                serialize();
            };

            const previewDownload = async () => {
                if (!form || !previewDownloadButton) {
                    return;
                }

                syncFormStateBeforeSubmit();

                const originalLabel = previewDownloadLabel?.textContent || 'Unduh PDF';
                previewDownloadButton.setAttribute('disabled', 'disabled');

                if (previewDownloadLabel) {
                    previewDownloadLabel.textContent = 'Sedang menyiapkan PDF...';
                }

                try {
                    const formData = new FormData(form);
                    formData.delete('_method');
                    const response = await fetch(previewDownloadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/pdf, application/json',
                        },
                        body: formData,
                    });

                    if (!response.ok) {
                        let message = 'Gagal mengunduh PDF.';

                        try {
                            const errorData = await response.json();
                            if (errorData?.message) {
                                message = errorData.message;
                            } else if (errorData?.errors) {
                                const firstError = Object.values(errorData.errors).flat()[0];
                                message = firstError || message;
                            }
                        } catch (error) {
                            // Biarkan pesan default jika respons error bukan JSON.
                        }

                        throw new Error(message);
                    }

                    const blob = await response.blob();
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    const filename = filenameFromDisposition(response.headers.get('Content-Disposition')) || 'sop-preview.pdf';

                    link.href = downloadUrl;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    window.URL.revokeObjectURL(downloadUrl);
                } catch (error) {
                    window.alert(error.message || 'Gagal mengunduh PDF.');
                } finally {
                    previewDownloadButton.removeAttribute('disabled');

                    if (previewDownloadLabel) {
                        previewDownloadLabel.textContent = originalLabel;
                    }
                }
            };

            document.querySelectorAll('[data-list-add]').forEach((button) => {
                button.addEventListener('click', () => {
                    const fieldName = button.dataset.listAdd;
                    const hiddenField = document.querySelector(`[name="${fieldName}"]`);
                    const values = splitLinesPreserveEmpty(hiddenField.value).filter((v, i, arr) => !(i === arr.length - 1 && v === ''));
                    values.push('');
                    hiddenField.value = values.join('\n');
                    renderListEditor(fieldName);
                });
            });

            const saveMasterExecutor = async () => {
                const label = String(masterExecutorInput?.value || '').trim();

                if (!label) {
                    setMasterExecutorFeedback('Isi nama pelaksana terlebih dahulu.', 'error');
                    return;
                }

                const key = normalizeExecutorKey(label);
                const existing = executors.find((item) => item.label.toLowerCase() === label.toLowerCase() || item.key === key);

                if (existing) {
                    setMasterExecutorFeedback('Pelaksana ini sudah ada di daftar tersimpan.', 'info');
                    if (masterExecutorInput) {
                        masterExecutorInput.value = existing.label;
                    }
                    masterExecutorMenu?.classList.add('hidden');
                    renderAll();
                    return;
                }

                addMasterExecutorButton?.setAttribute('disabled', 'disabled');
                addMasterExecutorButton?.classList.add('opacity-60', 'cursor-not-allowed');
                setMasterExecutorFeedback('Menyimpan pelaksana ke database...', 'info');

                try {
                    const response = await fetch(storeExecutorUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ name: label }),
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Gagal menyimpan pelaksana.');
                    }

                    executors.push(result.executor);
                    executors = executors.filter((executor, index, all) => all.findIndex((item) => item.key === executor.key) === index);

                    if (masterExecutorInput) {
                        masterExecutorInput.value = '';
                    }

                    masterExecutorMenu?.classList.add('hidden');
                    setMasterExecutorFeedback(result.message || 'Pelaksana berhasil disimpan.', 'success');
                    renderAll();
                } catch (error) {
                    setMasterExecutorFeedback(error.message || 'Gagal menyimpan pelaksana ke database.', 'error');
                } finally {
                    addMasterExecutorButton?.removeAttribute('disabled');
                    addMasterExecutorButton?.classList.remove('opacity-60', 'cursor-not-allowed');
                }
            };

            if (masterExecutorInput) {
                renderMasterExecutorResults(masterExecutorInput.value);

                masterExecutorInput.addEventListener('focus', () => {
                    renderMasterExecutorResults(masterExecutorInput.value);
                    masterExecutorMenu?.classList.remove('hidden');
                });

                masterExecutorInput.addEventListener('input', () => {
                    renderMasterExecutorResults(masterExecutorInput.value);
                    masterExecutorMenu?.classList.remove('hidden');
                });

                masterExecutorInput.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        saveMasterExecutor();
                    }
                });

                masterExecutorInput.addEventListener('blur', () => {
                    window.setTimeout(() => masterExecutorMenu?.classList.add('hidden'), 120);
                });
            }

            if (masterExecutorMenu) {
                masterExecutorMenu.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-executor-value]');

                    if (!button || !masterExecutorInput) {
                        return;
                    }

                    masterExecutorInput.value = button.dataset.executorValue || '';
                    masterExecutorMenu.classList.add('hidden');
                });
            }

            addMasterExecutorButton?.addEventListener('click', saveMasterExecutor);

            document.getElementById('add-activity-row').addEventListener('click', () => {
                activities.push(emptyActivity());
                renderAll();
            });

            document.querySelectorAll('.step-button').forEach((button) => {
                button.addEventListener('click', () => {
                    const target = button.dataset.stepTarget;
                    document.querySelectorAll('.step-panel').forEach((panel) => panel.classList.add('hidden'));
                    document.getElementById(target).classList.remove('hidden');

                    document.querySelectorAll('.step-button').forEach((item) => {
                        item.classList.remove(...stepActive);
                        item.classList.add(...stepInactive);
                        item.querySelector('span:last-child')?.classList.remove('text-white');
                        item.querySelector('span:last-child')?.classList.add('text-slate-800');
                        item.querySelector('span:first-child')?.classList.remove('text-slate-300');
                        item.querySelector('span:first-child')?.classList.add('text-slate-400');
                    });

                    button.classList.remove(...stepInactive);
                    button.classList.add(...stepActive);
                    button.querySelector('span:last-child')?.classList.remove('text-slate-800');
                    button.querySelector('span:last-child')?.classList.add('text-white');
                    button.querySelector('span:first-child')?.classList.remove('text-slate-400');
                    button.querySelector('span:first-child')?.classList.add('text-slate-300');
                });
            });

            replaceButton.addEventListener('click', () => {
                const search = replaceSearch.value.trim();

                if (!search) {
                    return;
                }

                document.querySelectorAll('input[name], textarea[name]').forEach((field) => {
                    field.value = String(field.value || '').split(search).join(replaceWith.value);
                });

                activities = activities.map((row) => ({
                    ...row,
                    name: (row.name || '').split(search).join(replaceWith.value),
                    flow_nodes: (row.flow_nodes || []).map((node) => ({
                        ...node,
                        label: (node.label || '').split(search).join(replaceWith.value),
                    })),
                    quality_requirements: (row.quality_requirements || []).map((item) => item.split(search).join(replaceWith.value)),
                    outputs: (row.outputs || []).map((item) => item.split(search).join(replaceWith.value)),
                    notes: (row.notes || '').split(search).join(replaceWith.value),
                }));

                renderListEditors();
                renderAll();
            });

            templateLoader.addEventListener('change', () => {
                const selected = templates.find((template) => String(template.id) === templateLoader.value);

                if (!selected) {
                    return;
                }

                const payload = selected.payload || {};
                const setValue = (name, value) => {
                    const field = document.querySelector(`[name="${name}"]`);
                    if (field) {
                        field.value = value || '';
                    }
                };

                setValue('sop_number', payload.sop_number || '');
                setValue('title', payload.title || '');
                setValue('year', payload.year || '');
                setValue('creation_date', payload.creation_date || '');
                setValue('revision_date', payload.revision_date || '');
                setValue('effective_date', payload.effective_date || '');
                setValue('approval_position', payload.approval_position || '');
                setValue('approval_name', payload.approval_name || '');
                setValue('approval_nip', payload.approval_nip || '');
                setValue('legal_basis_text', (payload.legal_basis || []).join('\n'));
                setValue('executor_qualifications_text', (payload.executor_qualifications || []).join('\n'));
                setValue('related_documents_text', (payload.related_documents || []).join('\n'));
                setValue('equipment_text', (payload.equipment || []).join('\n'));
                setValue('warnings_text', (payload.warnings || []).join('\n'));
                setValue('recording_text', (payload.recording || []).join('\n'));
                setValue('notes', payload.notes || '');

                executors = payload.executors || executors;
                activities = payload.activities || [];
                renderListEditors();
                renderAll();
            });

            document.querySelectorAll('[data-submit-status]').forEach((button) => {
                button.addEventListener('click', () => {
                    statusInput.value = button.dataset.submitStatus;
                    syncFormStateBeforeSubmit();
                    form.submit();
                });
            });

            previewDownloadButton?.addEventListener('click', previewDownload);

            if (activities.length === 0) {
                activities.push(emptyActivity());
            }

            renderListEditors();
            renderAll();
        });
    </script>
@endsection
