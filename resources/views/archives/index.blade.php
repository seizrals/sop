@extends('layouts.app')

@php
    $archiveRows = $documents
        ->map(function ($document) {
            preg_match('/(\d{4})(?!.*\d)/', (string) $document->sop_number, $matches);
            $displayYear = $matches[1] ?? $document->year;

            $updaterName = $document->updater?->name ?? $document->creator?->name ?? '-';

            return [
                'model' => $document,
                'title' => $document->title,
                'team' => $document->team?->display_name ?: '-',
                'activity' => $document->activity?->name ?: '-',
                'year' => $displayYear ?: '-',
                'status' => $document->status,
                'revision' => $document->status === 'revisi' ? ($document->revision_number ?: 1) : '-',
                'group_key' => $document->root_document_id ?: $document->id,
                'updater_name' => $updaterName,
                'sop_number' => $document->sop_number ?: '-',
            ];
        });

    $sopGroups = $archiveRows
        ->groupBy('group_key')
        ->map(function ($versions) {
            $sorted = $versions->sortByDesc(function ($document) {
                return [
                    $document['status'] === 'revisi' ? 2 : 1,
                    (int) ($document['model']->revision_number ?? 0),
                    $document['model']->updated_at?->timestamp ?? 0,
                    $document['model']->id,
                ];
            })->values();

            return [
                'latest' => $sorted->first(),
                'history' => $sorted->slice(1)->values(),
            ];
        })
        ->sortBy(function ($group) {
            $latest = $group['latest'] ?? [];
            return strtolower(($latest['activity'] ?? '-') . '|' . ($latest['title'] ?? '-'));
        })
        ->values();

    $finalGroups = $sopGroups
        ->map(function ($group) {
            $latest = $group['latest'];
            $history = $group['history'];

            if (($latest['status'] ?? null) === 'final') {
                return [
                    'latest' => $latest,
                    'history' => $history,
                ];
            }

            $historyFinal = $history->firstWhere('status', 'final');

            if (! $historyFinal) {
                return null;
            }

            return [
                'latest' => $historyFinal,
                'history' => $history
                    ->reject(fn ($row) => (int) $row['model']->id === (int) $historyFinal['model']->id)
                    ->values(),
            ];
        })
        ->filter()
        ->values();

    $formatFinalDate = function ($model) {
        $date = $model?->updated_at;

        if (! $date) {
            return '-';
        }

        return $date->timezone(config('app.timezone'))->format('d/m/Y');
    };
@endphp

@section('content')
    <div class="space-y-6">
        <section class="overflow-hidden rounded-[32px] border border-white/70 bg-white/85 p-6 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
            <form method="GET" action="{{ route('archives.index') }}" class="grid gap-4 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="q">Pencarian</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
                        </div>
                        <input
                            id="q"
                            type="search"
                            name="q"
                            value="{{ e($search) }}"
                            placeholder="Cari nama, nomor, tahun, atau catatan SOP..."
                            class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-12 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                        >
                        @if (filled($search))
                            <a href="{{ route('archives.index') }}" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-slate-600" title="Bersihkan pencarian">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                            </a>
                        @endif
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="team">Tim</label>
                    <select id="team" name="team" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                        <option value="">Semua tim</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected($selectedTeam === $team->id)>{{ $team->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="activity">Kegiatan</label>
                    <select id="activity" name="activity" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                        <option value="">Semua kegiatan</option>
                        @foreach ($activities as $activity)
                            <option value="{{ $activity->id }}" @selected($selectedActivity === $activity->id)>{{ $activity->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="inline-flex self-end items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" type="submit">Filter Arsip</button>
            </form>
        </section>

        <section class="overflow-hidden rounded-[32px] border border-white/70 bg-white/85 p-6 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-700">Arsip SOP</p>
            <h3 class="mt-2 text-2xl font-bold text-slate-900">Arsip seluruh dokumen SOP</h3>
            <p class="mt-2 text-sm leading-6 text-slate-500">Arsip hanya menampilkan dokumen SOP yang sudah disahkan (sudah ditandatangani kepala dan diunggah). Dokumen yang diunduh dan dilihat adalah versi final yang sudah bertanda tangan.</p>

            @if (filled($search))
                <div class="mt-6 inline-flex items-center gap-2 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-800">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
                    Hasil pencarian: <span class="font-bold">"{{ e($search) }}"</span>
                    @if ($finalGroups->isEmpty())
                        <span class="text-blue-600">— tidak ditemukan arsip yang sesuai.</span>
                    @else
                        <span class="text-blue-600">— {{ $groups->total() }} kelompok ditemukan.</span>
                    @endif
                </div>
            @endif

            @if ($finalGroups->isEmpty())
                <div class="mt-6 rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                    <h4 class="text-lg font-bold text-slate-900">Belum ada arsip dokumen</h4>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Dokumen akan tampil di arsip setelah SOP difinalisasi dan diunggah dokumen sahnya (yang sudah ditandatangani kepala) melalui menu Daftar SOP.</p>
                </div>
            @else
                <div class="mt-6 overflow-hidden rounded-[28px] border border-slate-200 bg-white">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50/90">
                            <tr>
                                <th class="px-5 py-4 text-left font-semibold text-slate-500">Nama SOP</th>
                                <th class="px-5 py-4 text-left font-semibold text-slate-500">Tim</th>
                                <th class="px-5 py-4 text-left font-semibold text-slate-500">Kegiatan</th>
                                <th class="px-5 py-4 text-left font-semibold text-slate-500">Terakhir Diperbarui Oleh</th>
                                <th class="px-5 py-4 text-left font-semibold text-slate-500">Tgl Finalisasi</th>
                                <th class="px-5 py-4 text-center font-semibold text-slate-500">Aksi</th>
                                <th class="px-5 py-4 text-center font-semibold text-slate-500">Riwayat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($finalGroups as $index => $sopGroup)
                                @php
                                    $latest = $sopGroup['latest'];
                                    $history = $sopGroup['history'];
                                    $historyId = 'history-' . $index;
                                @endphp
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-4">
                                        <div class="max-w-xl">
                                            <p class="text-base font-bold leading-7 text-slate-900">{{ $latest['title'] }}</p>
                                            <p class="mt-1 text-sm font-medium leading-6 text-slate-500">{{ $latest['sop_number'] }}</p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ $latest['team'] }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $latest['activity'] }}</td>
                                    <td class="px-5 py-4 text-slate-700">
                                        <div class="flex items-center gap-2">
                                            <div class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-[11px] font-semibold text-slate-600">
                                                {{ str($latest['updater_name'] ?? '-')->trim()->explode(' ')->filter()->take(2)->map(fn ($p) => str($p)->substr(0, 1)->upper())->implode('') ?: '-' }}
                                            </div>
                                            <span class="text-sm leading-5">{{ $latest['updater_name'] ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ $formatFinalDate($latest['model']) }}</td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="inline-flex items-center justify-center gap-2">
                                            <a href="{{ route('sop.signed-preview', $latest['model']) }}" target="_blank" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-100" title="Lihat PDF (versi disahkan)">
                                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </a>
                                            <a href="{{ route('sop.signed-download', $latest['model']) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100" title="Unduh PDF (versi disahkan)">
                                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 3v12"></path>
                                                    <path d="m7 10 5 5 5-5"></path>
                                                    <path d="M4 21h16"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if ($history->isNotEmpty())
                                            <button
                                                type="button"
                                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                                data-toggle-history="{{ $historyId }}"
                                                aria-expanded="false"
                                            >
                                                <span>Riwayat SOP</span>
                                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-600 transition" data-history-icon="{{ $historyId }}">▾</span>
                                            </button>
                                        @else
                                            <span class="text-sm text-slate-400">-</span>
                                        @endif
                                    </td>
                                </tr>

                                @if ($history->isNotEmpty())
                                    <tr class="hidden bg-slate-50/40" data-history-row="{{ $historyId }}">
                                        <td colspan="7" class="px-5 py-4">
                                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                                <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                                                    <p class="text-sm font-semibold text-slate-800">Riwayat untuk SOP: {{ $latest['title'] }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">Nomor SOP aktif {{ $latest['sop_number'] }} • Tim {{ $latest['team'] }} • Kegiatan {{ $latest['activity'] }}</p>
                                                </div>
                                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                                    <thead class="bg-slate-50/90">
                                                        <tr>
                                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Versi</th>
                                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Terakhir Diperbarui Oleh</th>
                                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Tgl Finalisasi</th>
                                                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-100 bg-white">
                                                        @foreach ($history as $historyRow)
                                                            <tr class="hover:bg-slate-50/70">
                                                                <td class="px-4 py-3 text-slate-700">
                                                                    <div>
                                                                        @if (($historyRow['revision'] ?? '-') !== '-')
                                                                            <p class="text-sm font-semibold">Revisi ke-{{ $historyRow['revision'] }}</p>
                                                                        @else
                                                                            <p class="text-sm font-semibold">Versi awal</p>
                                                                        @endif
                                                                        <p class="mt-1 text-xs font-medium text-slate-500">{{ $historyRow['sop_number'] }}</p>
                                                                    </div>
                                                                </td>
                                                                <td class="px-4 py-3 text-slate-700">
                                                                    <div class="flex items-center gap-2">
                                                                        <div class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-[10px] font-semibold text-slate-600">
                                                                            {{ str($historyRow['updater_name'] ?? '-')->trim()->explode(' ')->filter()->take(2)->map(fn ($p) => str($p)->substr(0, 1)->upper())->implode('') ?: '-' }}
                                                                        </div>
                                                                        <span class="text-xs leading-5">{{ $historyRow['updater_name'] ?? '-' }}</span>
                                                                    </div>
                                                                </td>
                                                                <td class="px-4 py-3 text-slate-600">{{ $formatFinalDate($historyRow['model']) }}</td>
                                                                <td class="px-4 py-3 text-center">
                                                                    <div class="inline-flex items-center justify-center gap-2">
                                                                        @if (filled($historyRow['model']->signed_file_path))
                                                                            <a href="{{ route('sop.signed-preview', $historyRow['model']) }}" target="_blank" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-100" title="Lihat PDF (versi disahkan)">
                                                                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                                                                                    <circle cx="12" cy="12" r="3"></circle>
                                                                                </svg>
                                                                            </a>
                                                                            <a href="{{ route('sop.signed-download', $historyRow['model']) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100" title="Unduh PDF (versi disahkan)">
                                                                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                                    <path d="M12 3v12"></path>
                                                                                    <path d="m7 10 5 5 5-5"></path>
                                                                                    <path d="M4 21h16"></path>
                                                                                </svg>
                                                                            </a>
                                                                        @else
                                                                            <span class="text-xs text-slate-400">Belum disahkan</span>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (isset($groups) && $groups->hasPages())
                    <div class="mt-6 flex flex-col items-start justify-between gap-4 border-t border-slate-100 px-5 py-5 sm:flex-row sm:items-center">
                        <p class="text-xs font-semibold leading-5 text-slate-500">
                            Menampilkan
                            <span class="font-bold text-slate-800">{{ $groups->firstItem() }}</span>
                            sampai
                            <span class="font-bold text-slate-800">{{ $groups->lastItem() }}</span>
                            dari
                            <span class="font-bold text-slate-800">{{ $groups->total() }}</span>
                            kelompok arsip.
                        </p>
                        <div>
                            {{ $groups->onEachSide(1)->links('pagination::tailwind') }}
                        </div>
                    </div>
                @endif
            @endif
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-toggle-history]').forEach((button) => {
                button.addEventListener('click', () => {
                    const key = button.getAttribute('data-toggle-history');
                    const row = document.querySelector(`[data-history-row="${key}"]`);
                    const icon = document.querySelector(`[data-history-icon="${key}"]`);
                    const isExpanded = button.getAttribute('aria-expanded') === 'true';
                    const nextExpanded = !isExpanded;

                    button.setAttribute('aria-expanded', nextExpanded ? 'true' : 'false');

                    if (row) {
                        row.classList.toggle('hidden', !nextExpanded);
                    }

                    if (icon) {
                        icon.style.transform = nextExpanded ? 'rotate(180deg)' : 'rotate(0deg)';
                    }
                });
            });
        });
    </script>
@endsection
