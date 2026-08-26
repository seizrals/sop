@extends('layouts.app')

@php
    $lautHero = \Illuminate\Support\Facades\Vite::asset('resources/img/laut-2.png');
    $lautCard = \Illuminate\Support\Facades\Vite::asset('resources/img/laut-3.png');

    $teamCards = $teams->map(function ($team) {
        return [
            'model' => $team,
            'display_name' => $team->display_name,
            'description' => $team->description ?: 'Kelola SOP dan dokumen turunan untuk tim ini.',
            'activities_count' => $team->activities_count,
            'sops_count' => $team->sops_count,
        ];
    });
@endphp

@section('content')
    <section class="relative overflow-hidden rounded-[34px] border border-white/40 bg-[linear-gradient(135deg,rgba(8,26,64,0.92)_0%,rgba(13,47,116,0.84)_55%,rgba(20,94,176,0.68)_100%)] p-7 text-white shadow-[0_35px_100px_-45px_rgba(8,26,64,0.5)] backdrop-blur-2xl">
        <div class="absolute inset-0 opacity-18" style="background-image: url('{{ $lautHero }}'); background-size: cover; background-position: center;"></div>
        <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(6,24,56,0.82)_0%,rgba(7,30,72,0.45)_48%,rgba(13,72,140,0.2)_100%)]"></div>

        <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-200">Menu SOP</p>
                <h3 class="mt-2 text-3xl font-bold text-white">Pilih tim pengelola SOP</h3>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-blue-50/85">Pilih tim untuk masuk ke daftar kegiatan dan dokumen SOP. Tampilan dibuat lebih fokus, bersih, dan elegan agar alur kerja terasa lebih nyaman digunakan.</p>
            </div>
            <div class="inline-flex rounded-full border border-white/14 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-cyan-100 backdrop-blur">
                {{ $teamCards->count() }} Tim
            </div>
        </div>
    </section>

    <section class="mt-6">
        @if ($teamCards->isEmpty())
            <div class="rounded-[30px] border border-dashed border-slate-300 bg-white/85 px-6 py-14 text-center shadow-[0_24px_70px_-40px_rgba(15,23,42,0.18)] backdrop-blur">
                <div class="mx-auto inline-flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-blue-500">
                    <i class="fa-solid fa-water text-2xl"></i>
                </div>
                <h4 class="mt-5 text-xl font-bold text-slate-900">Belum ada data tim</h4>
                <p class="mt-2 text-sm leading-6 text-slate-500">Data tim pengelola SOP belum tersedia di database. Silakan jalankan seeder atau tambahkan melalui panel admin.</p>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($teamCards as $team)
                    @php
                        $targetUrl = route('sop.team', $team['model']);
                    @endphp

                    <a
                        href="{{ $targetUrl }}"
                        class="group relative block overflow-hidden rounded-[30px] border border-white/75 bg-white/90 p-6 shadow-[0_24px_70px_-40px_rgba(15,23,42,0.18)] backdrop-blur-2xl transition duration-300 hover:-translate-y-1.5 hover:border-blue-200 hover:shadow-[0_28px_80px_-35px_rgba(37,99,235,0.28)]"
                    >
                        <div class="absolute inset-0 opacity-[0.08] transition duration-300 group-hover:opacity-[0.14]" style="background-image: url('{{ $lautCard }}'); background-size: cover; background-position: center;"></div>
                        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(255,255,255,0.88)_0%,rgba(243,248,255,0.94)_100%)]"></div>

                        <div class="relative">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h4 class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $team['display_name'] }}</h4>
                                    <p class="mt-4 text-sm leading-7 text-slate-500">{{ $team['description'] }}</p>
                                </div>
                                <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-[linear-gradient(135deg,#0f172a_0%,#2563eb_100%)] text-white shadow-[0_15px_35px_-20px_rgba(37,99,235,0.45)]">
                                    <i class="fa-solid fa-water"></i>
                                </span>
                            </div>

                            <div class="mt-6 grid grid-cols-2 gap-3">
                                <div class="rounded-[22px] border border-blue-100 bg-blue-50 px-4 py-4 text-center shadow-[0_15px_35px_-30px_rgba(59,130,246,0.18)]">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-500">Kegiatan</p>
                                    <p class="mt-2 text-2xl font-extrabold text-blue-900">{{ $team['activities_count'] }}</p>
                                </div>
                                <div class="rounded-[22px] border border-slate-200 bg-slate-100 px-4 py-4 text-center shadow-[0_15px_35px_-30px_rgba(15,23,42,0.1)]">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">SOP</p>
                                    <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $team['sops_count'] }}</p>
                                </div>
                            </div>

                            <div class="mt-7 flex justify-center">
                                <span class="inline-flex min-w-28 items-center justify-center rounded-2xl border border-slate-900 bg-slate-900 px-6 py-3 text-sm font-semibold text-white shadow-[0_15px_35px_-30px_rgba(15,23,42,0.28)] transition duration-300 group-hover:border-blue-300 group-hover:bg-blue-500 group-hover:text-white">
                                    Masuk
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection
