@extends('layouts.app')

@php
    $lautHero = \Illuminate\Support\Facades\Vite::asset('resources/img/laut-1.png');
    $lautPanel = \Illuminate\Support\Facades\Vite::asset('resources/img/laut-3.png');
    $previewStatusCounts = [
        'draft' => 21,
        'revisi' => 18,
        'final' => 57,
    ];

    $previewArchives = collect([
        ['year' => 2026, 'total' => 44],
        ['year' => 2025, 'total' => 39],
        ['year' => 2024, 'total' => 31],
        ['year' => 2023, 'total' => 20],
    ]);

    $previewLatest = collect([
        ['title' => 'SOP Pelaksanaan Statistik Air Bersih Tahunan', 'team' => 'Produksi', 'activity' => 'Air Bersih Tahunan', 'status' => 'final'],
        ['title' => 'SOP Pengolahan Sakernas Semesteran', 'team' => 'Social', 'activity' => 'Sakernas', 'status' => 'revisi'],
        ['title' => 'SOP Validasi Metadata Publikasi', 'team' => 'IPDS', 'activity' => 'Metadata Publikasi', 'status' => 'final'],
    ]);

    $resolvedStatusCounts = collect($statusCounts ?? $previewStatusCounts);
    $resolvedArchives = isset($archivesByYear) && $archivesByYear->count()
        ? $archivesByYear->map(fn ($row) => [
            'year' => (int) $row->year,
            'total' => (int) $row->total,
        ])
        : $previewArchives;
    $resolvedLatest = isset($latestDocuments) && $latestDocuments->count()
        ? $latestDocuments->map(fn ($document) => [
            'title' => $document->title,
            'team' => $document->team?->display_name ?? '-',
            'activity' => $document->activity?->name ?? '-',
            'status' => $document->status,
        ])
        : $previewLatest;

    $statusCards = [
        ['label' => 'Draft', 'value' => $resolvedStatusCounts->get('draft', $previewStatusCounts['draft'])],
        ['label' => 'Revisi', 'value' => $resolvedStatusCounts->get('revisi', $previewStatusCounts['revisi'])],
        ['label' => 'Final', 'value' => $resolvedStatusCounts->get('final', $previewStatusCounts['final'])],
    ];

    $archiveMap = $resolvedArchives->keyBy('year');
    $currentYear = now()->year;
    $archiveSeries = collect(range($currentYear - 4, $currentYear))
        ->map(fn ($year) => [
            'year' => $year,
            'total' => (int) data_get($archiveMap->get($year), 'total', 0),
        ]);
    $archiveMax = max(1, (int) $archiveSeries->max('total'));
    $greetingName = auth()->user()?->name ? \Illuminate\Support\Str::of(auth()->user()->name)->before(' ') : 'Pengguna';
@endphp

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-[34px] border border-white/40 bg-[linear-gradient(135deg,rgba(8,26,64,0.92)_0%,rgba(13,47,116,0.84)_54%,rgba(18,85,157,0.72)_100%)] p-7 text-white shadow-[0_35px_100px_-45px_rgba(8,26,64,0.48)] backdrop-blur-2xl">
            <div class="absolute inset-0 opacity-18" style="background-image: url('{{ $lautHero }}'); background-size: cover; background-position: center;"></div>
            <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(6,24,56,0.78)_0%,rgba(7,30,72,0.45)_46%,rgba(11,70,140,0.25)_100%)]"></div>
            <div class="grid gap-6 xl:grid-cols-[1.55fr_1fr] xl:items-stretch">
                <div class="relative flex h-full flex-col justify-between rounded-[30px] border border-white/14 bg-white/14 p-7 shadow-[0_20px_50px_-35px_rgba(0,0,0,0.22)] backdrop-blur-xl">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="inline-flex rounded-full border border-white/18 bg-white/10 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.3em] text-cyan-100">
                                Dashboard
                            </span>
                        </div>

                        <div class="mt-6 max-w-3xl">
                            <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                                Selamat datang, {{ $greetingName }}.
                            </h1>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-blue-50/85 sm:text-base">
                                Pantau progres dokumen SOP, distribusi arsip lima tahun terakhir, dan dokumen terbaru dalam satu halaman yang lebih hidup, elegan, dan selaras dengan identitas SOPERA.
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('sop.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-white/16 bg-white/12 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/18">
                            Buka Menu SOP
                        </a>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-[30px] border border-white/12 bg-[linear-gradient(135deg,rgba(10,39,98,0.78)_0%,rgba(20,76,173,0.65)_58%,rgba(44,154,222,0.56)_100%)] p-6 text-white shadow-[0_30px_90px_-40px_rgba(8,26,64,0.38)] backdrop-blur-xl">
                    <div class="absolute inset-0 opacity-25" style="background-image: url('{{ $lautPanel }}'); background-size: cover; background-position: center;"></div>
                    <div class="pointer-events-none absolute -right-8 top-6 h-28 w-28 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="pointer-events-none absolute bottom-0 left-0 h-32 w-32 rounded-full bg-cyan-300/15 blur-3xl"></div>

                    <div class="relative h-full">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-100">Status Hari Ini</p>
                        <h2 class="mt-3 text-2xl font-bold">Kondisi dokumen SOP saat ini</h2>

                        <div class="mt-6 grid gap-3">
                            @foreach ($statusCards as $status)
                                <div class="flex items-center justify-between rounded-[24px] border border-white/10 bg-white/10 px-5 py-4 backdrop-blur">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-100">{{ $status['label'] }}</p>
                                        <p class="mt-1 text-sm text-blue-100/80">Dokumen dengan status {{ strtolower($status['label']) }}</p>
                                    </div>
                                    <p class="text-3xl font-bold">{{ $status['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <section class="relative overflow-hidden rounded-[34px] border border-white/70 bg-white/92 p-6 shadow-[0_30px_90px_-45px_rgba(15,23,42,0.18)] backdrop-blur-2xl">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-700">Distribusi Arsip</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-900">Diagram dokumen per tahun</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Menampilkan distribusi arsip SOP lima tahun terakhir, termasuk tahun dengan nilai 0.</p>
                    </div>
                    <a href="{{ route('archives.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white/85 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-white">
                        Buka Arsip
                    </a>
                </div>

                <div class="relative mt-8 grid grid-cols-5 gap-4">
                    @foreach ($archiveSeries as $archive)
                        <div class="rounded-[26px] border border-slate-200 bg-white p-4 shadow-[0_15px_35px_-28px_rgba(15,23,42,0.12)]">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-slate-700">{{ $archive['year'] }}</p>
                                <span class="text-xs font-semibold text-slate-400">{{ $archive['total'] }}</span>
                            </div>

                            <div class="mt-5 flex h-40 items-end rounded-[20px] bg-slate-50 p-2">
                                <div class="relative h-full w-full overflow-hidden rounded-[18px] bg-white">
                                    @if ($archive['total'] > 0)
                                        <div
                                            class="absolute inset-x-0 bottom-0 rounded-[18px] bg-[linear-gradient(180deg,#3b82f6_0%,#2563eb_100%)]"
                                            style="height: {{ (int) round(($archive['total'] / $archiveMax) * 100) }}%;"
                                        ></div>
                                    @endif
                                </div>
                            </div>

                            <p class="mt-4 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Dokumen</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="relative overflow-hidden rounded-[34px] border border-white/70 bg-white/92 p-6 shadow-[0_30px_90px_-45px_rgba(15,23,42,0.18)] backdrop-blur-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-700">Update Terbaru</p>
                <h3 class="mt-2 text-2xl font-bold text-slate-900">Dokumen yang terakhir dipantau</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">Ringkasan dokumen terbaru yang paling relevan untuk dipantau dari dashboard.</p>

                <div class="relative mt-6 space-y-4">
                    @foreach ($resolvedLatest->take(5) as $document)
                        <div class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-[0_15px_35px_-28px_rgba(15,23,42,0.12)]">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $document['title'] }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $document['team'] }} - {{ $document['activity'] }}</p>
                                </div>
                                <span @class([
                                    'rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em]',
                                    'bg-emerald-100 text-emerald-700' => $document['status'] === 'final',
                                    'bg-amber-100 text-amber-700' => $document['status'] === 'revisi',
                                    'bg-slate-900 text-white' => ! in_array($document['status'], ['final', 'revisi']),
                                ])>
                                    {{ strtoupper($document['status']) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </section>
    </div>
@endsection
