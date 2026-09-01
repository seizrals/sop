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
                                            <a href="{{ route('sop.preview', $document['model']) }}" target="_blank" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-100" title="Lihat PDF">
                                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </a>
                                            <a
                                                href="{{ route('sop.download', $document['model']) }}"
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100 disabled:cursor-not-allowed disabled:border-emerald-100 disabled:bg-emerald-100 disabled:text-emerald-500"
                                                data-download-button
                                                title="Unduh PDF"
                                            >
                                                <span class="hidden" data-download-label>Unduh</span>
                                                <svg data-download-icon viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 3v12"></path>
                                                    <path d="m7 10 5 5 5-5"></path>
                                                    <path d="M4 21h16"></path>
                                                </svg>
                                            </a>
                                            @if ($document['status'] !== 'final' || $isAdmin)
                                                <form method="POST" action="{{ route('sop.destroy', $document['model']) }}" class="inline" onsubmit="{{ $document['status'] === 'final' ? 'return confirm(\'Anda yakin menghapus SOP FINAL? Tindakan ini tidak dapat dibatalkan.\')' : '' }}">
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
                                        <td colspan="6" class="px-5 py-4">
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
                                                                            <a href="{{ route('sop.preview', $historyItem['model']) }}" target="_blank" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-100" title="Lihat PDF">
                                                                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                                                                                    <circle cx="12" cy="12" r="3"></circle>
                                                                                </svg>
                                                                            </a>
                                                                            <a
                                                                                href="{{ route('sop.download', $historyItem['model']) }}"
                                                                                class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100"
                                                                                data-download-button
                                                                                title="Unduh PDF"
                                                                            >
                                                                                <span class="hidden" data-download-label>Unduh</span>
                                                                                <svg data-download-icon viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                                    <path d="M12 3v12"></path>
                                                                                    <path d="m7 10 5 5 5-5"></path>
                                                                                    <path d="M4 21h16"></path>
                                                                                </svg>
                                                                            </a>
                                                                            @if (($historyItem['status'] ?? 'draft') !== 'final' || $isAdmin)
                                                                                <form method="POST" action="{{ route('sop.destroy', $historyItem['model']) }}" class="inline" onsubmit="{{ ($historyItem['status'] ?? 'draft') === 'final' ? 'return confirm(\'Anda yakin menghapus SOP FINAL? Tindakan ini tidak dapat dibatalkan.\')' : '' }}">
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
        });
    </script>
@endsection
