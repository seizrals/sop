@extends('layouts.app')

@php
    $authUser = Auth::user();
    $isAdmin = $authUser?->role === 'admin';
    $statusClass = [
        'draft' => 'border-slate-200 bg-slate-50 text-slate-700',
        'revisi' => 'border-amber-200 bg-amber-50 text-amber-700',
        'final' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'simpan' => 'border-slate-200 bg-slate-50 text-slate-700',
    ];

    $normalizedDocuments = $documents->map(function ($document) {
        preg_match('/(\d{4})(?!.*\d)/', (string) $document->sop_number, $matches);

        $status = $document->status === 'simpan' ? 'draft' : $document->status;
        $rootKey = $document->root_document_id ?: $document->id;
        $updatedAt = $document->updated_at?->timestamp ?? $document->created_at?->timestamp ?? 0;

        return [
            'root_key' => $rootKey,
            'model' => $document,
            'title' => $document->title,
            'sop_number' => $document->sop_number ?: '-',
            'year' => $matches[1] ?? $document->year,
            'status' => $status,
            'revision_number' => $document->revision_number,
            'short_title' => \Illuminate\Support\Str::limit($document->title, 56),
            'updated_at' => $updatedAt,
        ];
    });

    $groupedDocuments = $normalizedDocuments
        ->groupBy('root_key')
        ->map(function ($items) {
            return $items
                ->sortByDesc(fn ($item) => ($item['revision_number'] ?? 0) * 10000000000 + ($item['updated_at'] ?? 0))
                ->values();
        })
        ->sortByDesc(function ($items) {
            $first = $items->first();
            return (data_get($first, 'revision_number', 0) * 10000000000) + (data_get($first, 'updated_at', 0));
        })
        ->values();
@endphp

@section('content')
    <div class="space-y-6">
        <section class="overflow-hidden rounded-[32px] border border-white/70 bg-white/85 p-6 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-700">{{ $team->display_name }} / {{ $activity->name }}</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">Daftar SOP</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Kelola SOP untuk kegiatan ini, lanjutkan draft yang masih dikerjakan, unduh dokumen, atau buat revisi baru saat diperlukan.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('sop.team', $team) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Kembali ke Kegiatan</a>
                    <a href="{{ route('sop.create', [$team, $activity]) }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Buat SOP</a>
                </div>
            </div>

            @if ($groupedDocuments->isEmpty())
                <div class="mt-6 rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                    <h4 class="text-lg font-bold text-slate-900">Belum ada SOP untuk kegiatan ini</h4>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Mulai dengan membuat SOP baru atau gunakan template dari menu template SOP.</p>
                    <div class="mt-5 flex justify-center">
                        <a href="{{ route('sop.create', [$team, $activity]) }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Buat SOP</a>
                    </div>
                </div>
            @else
                <div class="mt-6 overflow-hidden rounded-[28px] border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50/90">
                            <tr>
                                <th class="px-5 py-4 text-left font-semibold text-slate-500">Nama SOP</th>
                                <th class="px-5 py-4 text-left font-semibold text-slate-500">Nomor SOP</th>
                                <th class="px-5 py-4 text-left font-semibold text-slate-500">Tahun</th>
                                <th class="px-5 py-4 text-left font-semibold text-slate-500">Status</th>
                                <th class="px-5 py-4 text-center font-semibold text-slate-500">Revisi</th>
                                <th class="px-5 py-4 text-center font-semibold text-slate-500">Disahkan</th>
                                <th class="px-5 py-4 text-right font-semibold text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($groupedDocuments as $groupIndex => $groupItems)
                                @php
                                    $document = $groupItems->first();
                                    $history = $groupItems->slice(1)->values();
                                    $historyId = 'history-' . $document['root_key'];
                                @endphp
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-4">
                                        <div class="max-w-xl">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="inline-flex rounded-full bg-slate-900 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-white">SOP</span>
                                                <span class="text-xs font-medium text-slate-400">Dokumen {{ $document['year'] }}</span>
                                                @if ($history->isNotEmpty())
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                                                        title="Klik untuk melihat riwayat SOP"
                                                        data-history-toggle="{{ $historyId }}"
                                                    >
                                                        Riwayat ({{ $history->count() }})
                                                    </button>
                                                @endif
                                            </div>
                                            <p class="mt-3 text-base font-bold leading-7 text-slate-900">{{ $document['title'] }}</p>
                                            <p class="mt-1 text-sm leading-6 text-slate-500">{{ $document['short_title'] !== $document['title'] ? $document['short_title'] : 'Dokumen SOP untuk kegiatan ' . $activity->name }}</p>
                                        </div>
                                        @if ($document['status'] === 'final' && $document['revision_number'] > 0)
                                            <p class="mt-2 inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Revisi ke-{{ $document['revision_number'] }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-slate-500">{{ $document['sop_number'] }}</td>
                                    <td class="px-5 py-4 text-slate-500">{{ $document['year'] }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $statusClass[$document['status']] ?? 'border-slate-200 bg-slate-50 text-slate-700' }}">
                                            {{ strtoupper($document['status']) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if ($document['status'] === 'final')
                                            <form method="POST" action="{{ route('sop.revise', $document['model']) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="revision_year" value="{{ now()->year }}">
                                                <button class="inline-flex items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100" type="submit">Revisi</button>
                                            </form>
                                        @else
                                            <span class="text-sm text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @php
                                            $hasSigned = filled($document['model']->signed_file_path);
                                        @endphp
                                        @if ($document['status'] !== 'final')
                                            <span class="text-sm text-slate-400">-</span>
                                        @elseif ($hasSigned)
                                            <div class="flex flex-col items-center gap-2">
                                                <span class="inline-flex rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-violet-700" title="Diupload pada {{ optional($document['model']->signed_at)->timezone(config('app.timezone'))->format('d/m/Y H:i') }}">
                                                    <svg viewBox="0 0 24 24" class="mr-1.5 h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><path d="m9 11 3 3L22 4"></path></svg>
                                                    Sudah Disahkan
                                                </span>
                                                <div class="flex flex-wrap justify-center gap-1">
                                                    <a href="{{ route('sop.signed-preview', $document['model']) }}" target="_blank" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-100" title="Lihat dokumen disahkan">
                                                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                    </a>
                                                    <a href="{{ route('sop.signed-download', $document['model']) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100" title="Unduh dokumen disahkan">
                                                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M12 3v12"></path>
                                                            <path d="m7 10 5 5 5-5"></path>
                                                            <path d="M4 21h16"></path>
                                                        </svg>
                                                    </a>
                                                    <button
                                                        type="button"
                                                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-amber-200 bg-amber-50 text-amber-700 transition hover:bg-amber-100"
                                                        data-open-upload
                                                        data-document-id="{{ $document['model']->id }}"
                                                        data-document-title="{{ $document['title'] }}"
                                                        title="Unggah ulang dokumen disahkan"
                                                    >
                                                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                            <path d="M17 8l-5-5-5 5"></path>
                                                            <path d="M12 3v12"></path>
                                                        </svg>
                                                    </button>
                                                    @if ($isAdmin)
                                                        <form method="POST" action="{{ route('sop.signed-delete', $document['model']) }}" class="inline" data-delete-confirm data-delete-title="Hapus Dokumen Disahkan" data-delete-message="Anda yakin menghapus dokumen SOP yang disahkan?">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-red-200 bg-red-50 text-red-700 transition hover:bg-red-100" type="submit" title="Hapus dokumen disahkan">
                                                                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                    <path d="M3 6h18"></path>
                                                                    <path d="M8 6V4h8v2"></path>
                                                                    <path d="M19 6l-1 14H6L5 6"></path>
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <button
                                                type="button"
                                                class="inline-flex items-center justify-center gap-1.5 rounded-2xl border border-violet-300 bg-violet-50 px-3 py-2 text-sm font-semibold text-violet-700 transition hover:border-violet-400 hover:bg-violet-100"
                                                data-open-upload
                                                data-document-id="{{ $document['model']->id }}"
                                                data-document-title="{{ $document['title'] }}"
                                            >
                                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                    <path d="M17 8l-5-5-5 5"></path>
                                                    <path d="M12 3v12"></path>
                                                </svg>
                                                Unggah Dokumen
                                            </button>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            @if ($document['status'] !== 'final')
                                                <a href="{{ route('sop.edit', $document['model']) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50" title="Edit SOP">
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M12 20h9"></path>
                                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"></path>
                                                    </svg>
                                                </a>
                                            @endif
                                            <a href="{{ route('sop.preview', $document['model']) }}" target="_blank" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-100" title="Lihat PDF (draft final)">
                                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </a>
                                            <a
                                                href="{{ route('sop.download', $document['model']) }}"
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100 disabled:cursor-not-allowed disabled:border-emerald-100 disabled:bg-emerald-100 disabled:text-emerald-500"
                                                data-download-button
                                                title="Unduh PDF (draft final)"
                                            >
                                                <span class="hidden" data-download-label>Unduh</span>
                                                <svg data-download-icon viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 3v12"></path>
                                                    <path d="m7 10 5 5 5-5"></path>
                                                    <path d="M4 21h16"></path>
                                                </svg>
                                            </a>
                                            @if ($document['status'] !== 'final' || $isAdmin)
                                                <form method="POST" action="{{ route('sop.destroy', $document['model']) }}" class="inline" @if ($document['status'] === 'final') data-delete-confirm data-delete-title="Hapus SOP FINAL" data-delete-message="Anda yakin menghapus SOP FINAL? Tindakan ini tidak dapat dibatalkan." @endif>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border {{ $document['status'] === 'final' ? 'border-red-400 bg-red-100 text-red-800' : 'border-red-200 bg-red-50 text-red-700' }} transition hover:bg-red-100" type="submit" title="{{ $document['status'] === 'final' ? 'Hapus SOP Final (Admin Only)' : 'Hapus SOP' }}">
                                                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M3 6h18"></path>
                                                            <path d="M8 6V4h8v2"></path>
                                                            <path d="M19 6l-1 14H6L5 6"></path>
                                                            <path d="M10 11v6"></path>
                                                            <path d="M14 11v6"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @if ($history->isNotEmpty())
                                    <tr id="{{ $historyId }}" class="hidden bg-slate-50/50">
                                        <td colspan="7" class="px-5 py-4">
                                            <div class="rounded-[20px] border border-slate-200 bg-white p-4">
                                                <div class="flex items-center justify-between gap-3">
                                                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Riwayat SOP</p>
                                                    <button type="button" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50" data-history-close="{{ $historyId }}">Tutup</button>
                                                </div>
                                                <div class="mt-4 overflow-hidden rounded-[18px] border border-slate-200">
                                                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                                                        <thead class="bg-slate-50/90">
                                                            <tr>
                                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Versi</th>
                                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Nomor SOP</th>
                                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Tahun</th>
                                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Status</th>
                                                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Revisi</th>
                                                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Disahkan</th>
                                                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-slate-100 bg-white">
                                                            @foreach ($history as $historyItem)
                                                                <tr class="hover:bg-slate-50/70">
                                                                    <td class="px-4 py-3 text-slate-700">
                                                                        @if (($historyItem['revision_number'] ?? 0) > 0)
                                                                            Revisi ke-{{ $historyItem['revision_number'] }}
                                                                        @else
                                                                            Versi awal
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-4 py-3 text-slate-500">{{ $historyItem['sop_number'] }}</td>
                                                                    <td class="px-4 py-3 text-slate-500">{{ $historyItem['year'] }}</td>
                                                                    <td class="px-4 py-3">
                                                                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $statusClass[$historyItem['status']] ?? 'border-slate-200 bg-slate-50 text-slate-700' }}">
                                                                            {{ strtoupper($historyItem['status']) }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="px-4 py-3 text-center">
                                                                        @if (($historyItem['status'] ?? 'draft') === 'final')
                                                                            <form method="POST" action="{{ route('sop.revise', $historyItem['model']) }}" class="inline">
                                                                                @csrf
                                                                                <input type="hidden" name="revision_year" value="{{ now()->year }}">
                                                                                <button class="inline-flex items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100" type="submit">Revisi</button>
                                                                            </form>
                                                                        @else
                                                                            <span class="text-sm text-slate-400">-</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-4 py-3 text-center">
                                                                        @php
                                                                            $hasSignedHistory = filled($historyItem['model']->signed_file_path);
                                                                        @endphp
                                                                        @if (($historyItem['status'] ?? 'draft') !== 'final')
                                                                            <span class="text-sm text-slate-400">-</span>
                                                                        @elseif ($hasSignedHistory)
                                                                            <div class="flex flex-col items-center gap-1">
                                                                                <span class="inline-flex rounded-full border border-violet-200 bg-violet-50 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-[0.15em] text-violet-700">
                                                                                    <svg viewBox="0 0 24 24" class="mr-1 h-2.5 w-2.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><path d="m9 11 3 3L22 4"></path></svg>
                                                                                    Sudah Disahkan
                                                                                </span>
                                                                                <div class="flex flex-wrap justify-center gap-1">
                                                                                    <a href="{{ route('sop.signed-preview', $historyItem['model']) }}" target="_blank" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-100" title="Lihat dokumen disahkan">
                                                                                        <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                                                                                            <circle cx="12" cy="12" r="3"></circle>
                                                                                        </svg>
                                                                                    </a>
                                                                                    <a href="{{ route('sop.signed-download', $historyItem['model']) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100" title="Unduh dokumen disahkan">
                                                                                        <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                                            <path d="M12 3v12"></path>
                                                                                            <path d="m7 10 5 5 5-5"></path>
                                                                                            <path d="M4 21h16"></path>
                                                                                        </svg>
                                                                                    </a>
                                                                                    <button
                                                                                        type="button"
                                                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-amber-200 bg-amber-50 text-amber-700 transition hover:bg-amber-100"
                                                                                        data-open-upload
                                                                                        data-document-id="{{ $historyItem['model']->id }}"
                                                                                        data-document-title="{{ $historyItem['title'] }}"
                                                                                        title="Unggah ulang"
                                                                                    >
                                                                                        <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                                                            <path d="M17 8l-5-5-5 5"></path>
                                                                                            <path d="M12 3v12"></path>
                                                                                        </svg>
                                                                                    </button>
                                                                                    @if ($isAdmin)
                                                                                        <form method="POST" action="{{ route('sop.signed-delete', $historyItem['model']) }}" class="inline" data-delete-confirm data-delete-title="Hapus Dokumen Disahkan" data-delete-message="Anda yakin menghapus dokumen SOP yang disahkan?">
                                                                                            @csrf
                                                                                            @method('DELETE')
                                                                                            <button class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-700 transition hover:bg-red-100" type="submit" title="Hapus">
                                                                                                <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                                                    <path d="M3 6h18"></path>
                                                                                                    <path d="M8 6V4h8v2"></path>
                                                                                                    <path d="M19 6l-1 14H6L5 6"></path>
                                                                                                </svg>
                                                                                            </button>
                                                                                        </form>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        @else
                                                                            <button
                                                                                type="button"
                                                                                class="inline-flex items-center justify-center gap-1 rounded-xl border border-violet-300 bg-violet-50 px-2.5 py-1.5 text-xs font-semibold text-violet-700 transition hover:border-violet-400 hover:bg-violet-100"
                                                                                data-open-upload
                                                                                data-document-id="{{ $historyItem['model']->id }}"
                                                                                data-document-title="{{ $historyItem['title'] }}"
                                                                            >
                                                                                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                                                    <path d="M17 8l-5-5-5 5"></path>
                                                                                    <path d="M12 3v12"></path>
                                                                                </svg>
                                                                                Unggah
                                                                            </button>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-4 py-3">
                                                                        <div class="flex flex-wrap justify-end gap-2">
                                                                            @if (($historyItem['status'] ?? 'draft') !== 'final')
                                                                                <a href="{{ route('sop.edit', $historyItem['model']) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50" title="Edit SOP">
                                                                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                                        <path d="M12 20h9"></path>
                                                                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"></path>
                                                                                    </svg>
                                                                                </a>
                                                                            @endif
                                                                            <a href="{{ route('sop.preview', $historyItem['model']) }}" target="_blank" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-100" title="Lihat PDF (draft final)">
                                                                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                                                                                    <circle cx="12" cy="12" r="3"></circle>
                                                                                </svg>
                                                                            </a>
                                                                            <a
                                                                                href="{{ route('sop.download', $historyItem['model']) }}"
                                                                                class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100"
                                                                                data-download-button
                                                                                title="Unduh PDF (draft final)"
                                                                            >
                                                                                <span class="hidden" data-download-label>Unduh</span>
                                                                                <svg data-download-icon viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                                    <path d="M12 3v12"></path>
                                                                                    <path d="m7 10 5 5 5-5"></path>
                                                                                    <path d="M4 21h16"></path>
                                                                                </svg>
                                                                            </a>
                                                                            @if (($historyItem['status'] ?? 'draft') !== 'final' || $isAdmin)
                                                                                <form method="POST" action="{{ route('sop.destroy', $historyItem['model']) }}" class="inline" @if (($historyItem['status'] ?? 'draft') === 'final') data-delete-confirm data-delete-title="Hapus SOP FINAL" data-delete-message="Anda yakin menghapus SOP FINAL? Tindakan ini tidak dapat dibatalkan." @endif>
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <button class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border {{ ($historyItem['status'] ?? 'draft') === 'final' ? 'border-red-400 bg-red-100 text-red-800' : 'border-red-200 bg-red-50 text-red-700' }} transition hover:bg-red-100" type="submit" title="{{ ($historyItem['status'] ?? 'draft') === 'final' ? 'Hapus SOP Final (Admin Only)' : 'Hapus SOP' }}">
                                                                                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                                            <path d="M3 6h18"></path>
                                                                                            <path d="M8 6V4h8v2"></path>
                                                                                            <path d="M19 6l-1 14H6L5 6"></path>
                                                                                            <path d="M10 11v6"></path>
                                                                                            <path d="M14 11v6"></path>
                                                                                        </svg>
                                                                                    </button>
                                                                                </form>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    <div id="upload-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" aria-hidden="true" role="dialog" aria-modal="true">
        <div id="upload-modal-backdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative z-10 w-full max-w-lg overflow-hidden rounded-[32px] border border-white/80 bg-white shadow-[0_30px_80px_-35px_rgba(15,23,42,0.45)]">
            <div class="border-b border-slate-100 px-8 py-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-violet-600">Unggah Dokumen</p>
                        <h3 id="upload-modal-title" class="mt-2 text-xl font-bold text-slate-900">Unggah Dokumen SOP Disahkan</h3>
                        <p id="upload-modal-subtitle" class="mt-2 text-sm leading-6 text-slate-500">Pilih berkas PDF SOP yang sudah ditandatangani kepala.</p>
                    </div>
                    <button id="upload-modal-close" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-700" aria-label="Tutup">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form id="upload-modal-form" method="POST" enctype="multipart/form-data" class="px-8 py-6">
                @csrf
                <div class="rounded-[24px] border-2 border-dashed border-slate-200 bg-slate-50 p-6 text-center transition hover:border-violet-300 hover:bg-violet-50/40">
                    <div class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-2xl border border-violet-200 bg-violet-50 text-violet-600">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <path d="M17 8l-5-5-5 5"></path>
                            <path d="M12 3v12"></path>
                        </svg>
                    </div>
                    <h4 class="mt-4 text-sm font-semibold text-slate-800">Pilih berkas PDF</h4>
                    <p class="mt-1 text-xs leading-5 text-slate-500">Seret berkas ke sini, atau klik untuk memilih dari perangkat Anda.<br>Maksimal 20 MB • Format PDF</p>
                    <input id="upload-modal-file" type="file" name="signed_file" accept="application/pdf" required class="mt-4 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800">
                </div>
                <div id="upload-modal-file-info" class="mt-3 hidden rounded-2xl border border-violet-100 bg-violet-50 px-4 py-3 text-sm text-violet-700"></div>
                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button id="upload-modal-cancel" type="button" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Batal</button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 focus:outline-none focus:ring-4 focus:ring-violet-100">
                        <svg viewBox="0 0 24 24" class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <path d="M17 8l-5-5-5 5"></path>
                            <path d="M12 3v12"></path>
                        </svg>
                        Unggah Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-download-button]').forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();

                    if (button.dataset.loading === 'true') {
                        return;
                    }

                    const label = button.querySelector('[data-download-label]');
                    const originalLabel = label?.textContent || 'Unduh';
                    const icon = button.querySelector('[data-download-icon]');
                    const originalTitle = button.getAttribute('title') || 'Unduh PDF';

                    button.dataset.loading = 'true';
                    button.setAttribute('aria-disabled', 'true');
                    button.classList.add('pointer-events-none');
                    button.setAttribute('title', 'Sedang mengunduh...');

                    if (label) {
                        label.textContent = 'Sedang mengunduh...';
                    }

                    if (icon) {
                        icon.classList.add('animate-pulse');
                    }

                    window.location.href = button.href;

                    window.setTimeout(() => {
                        button.dataset.loading = 'false';
                        button.removeAttribute('aria-disabled');
                        button.classList.remove('pointer-events-none');

                        if (label) {
                            label.textContent = originalLabel;
                        }

                        if (icon) {
                            icon.classList.remove('animate-pulse');
                        }

                        button.setAttribute('title', originalTitle);
                    }, 2500);
                });
            });

            document.querySelectorAll('[data-history-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.dataset.historyToggle;
                    const panel = id ? document.getElementById(id) : null;
                    if (!panel) {
                        return;
                    }
                    panel.classList.toggle('hidden');
                });
            });

            document.querySelectorAll('[data-history-close]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.dataset.historyClose;
                    const panel = id ? document.getElementById(id) : null;
                    panel?.classList.add('hidden');
                });
            });

            const uploadModal = document.getElementById('upload-modal');
            const uploadModalForm = document.getElementById('upload-modal-form');
            const uploadModalFile = document.getElementById('upload-modal-file');
            const uploadModalFileInfo = document.getElementById('upload-modal-file-info');
            const uploadModalTitle = document.getElementById('upload-modal-title');
            const uploadModalSubtitle = document.getElementById('upload-modal-subtitle');
            const uploadModalClose = document.getElementById('upload-modal-close');
            const uploadModalBackdrop = document.getElementById('upload-modal-backdrop');
            const uploadModalCancel = document.getElementById('upload-modal-cancel');

            const routeBaseUploadSigned = "{{ route('sop.upload-signed', '__DOC_ID__') }}";

            function formatBytes(bytes, decimals = 2) {
                if (!+bytes) return '0 Bytes';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
            }

            function openUploadModal(documentId, documentTitle) {
                if (!uploadModal || !uploadModalForm) return;
                const action = routeBaseUploadSigned.replace('__DOC_ID__', documentId);
                uploadModalForm.setAttribute('action', action);
                uploadModalForm.reset();
                uploadModalFileInfo?.classList.add('hidden');
                uploadModalFileInfo && (uploadModalFileInfo.textContent = '');
                if (uploadModalTitle) uploadModalTitle.textContent = 'Unggah Dokumen SOP Disahkan';
                if (uploadModalSubtitle) uploadModalSubtitle.innerHTML = `Unggah berkas PDF untuk SOP: <strong class="text-slate-700">${documentTitle || '-'}</strong>`;
                uploadModal.classList.remove('hidden');
                uploadModal.classList.add('flex');
                uploadModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closeUploadModal() {
                if (!uploadModal) return;
                uploadModal.classList.add('hidden');
                uploadModal.classList.remove('flex');
                uploadModal.setAttribute('aria-hidden', 'true');
                uploadModalForm && uploadModalForm.reset();
                uploadModalFileInfo?.classList.add('hidden');
                document.body.style.overflow = '';
            }

            document.querySelectorAll('[data-open-upload]').forEach((button) => {
                button.addEventListener('click', () => {
                    const documentId = button.getAttribute('data-document-id');
                    const documentTitle = button.getAttribute('data-document-title') || '';
                    openUploadModal(documentId, documentTitle);
                });
            });

            uploadModalClose?.addEventListener('click', closeUploadModal);
            uploadModalCancel?.addEventListener('click', closeUploadModal);
            uploadModalBackdrop?.addEventListener('click', closeUploadModal);
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && uploadModal && !uploadModal.classList.contains('hidden')) {
                    closeUploadModal();
                }
            });

            uploadModalFile?.addEventListener('change', () => {
                if (!uploadModalFile || !uploadModalFileInfo) return;
                const file = uploadModalFile.files?.[0];
                if (file) {
                    uploadModalFileInfo.classList.remove('hidden');
                    uploadModalFileInfo.textContent = `Berkas terpilih: ${file.name} (${formatBytes(file.size)})`;
                } else {
                    uploadModalFileInfo.classList.add('hidden');
                }
            });
        });
    </script>
@endsection
