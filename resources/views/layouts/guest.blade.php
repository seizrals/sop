<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($pageTitle) ? $pageTitle . ' - SOPERA (SOP Generator)' : 'SOPERA (SOP Generator)' }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900">
        @php
            $logoBps = asset('resources/img/logo-bps.png');
            $lautHero = asset('resources/img/laut-2.png');
        @endphp

        <div class="relative min-h-screen overflow-hidden bg-[linear-gradient(180deg,#dce6f4_0%,#e8eff8_42%,#dfe8f4_100%)]">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(37,99,235,0.16),transparent_40%)]"></div>
            <div class="pointer-events-none absolute -left-20 top-10 h-72 w-72 rounded-full bg-cyan-300/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -right-20 bottom-10 h-80 w-80 rounded-full bg-blue-400/16 blur-3xl"></div>

            <div class="relative mx-auto flex min-h-screen max-w-[1600px] items-center justify-center p-4 sm:p-6 lg:p-8">
                <div class="w-full">
                    <section class="relative overflow-hidden rounded-[40px] border border-white/30 bg-[linear-gradient(145deg,rgba(8,26,64,0.94)_0%,rgba(13,47,116,0.9)_52%,rgba(21,94,178,0.74)_100%)] p-6 text-white shadow-[0_45px_120px_-50px_rgba(8,26,64,0.62)] sm:p-8 lg:min-h-[760px] lg:p-10">
                        <div class="absolute inset-0 opacity-30" style="background-image: url('{{ $lautHero }}'); background-size: cover; background-position: center;"></div>
                        <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(6,24,56,0.78)_0%,rgba(8,30,72,0.4)_48%,rgba(10,67,140,0.16)_100%)]"></div>

                        <div class="absolute left-6 top-6 z-10 sm:left-8 sm:top-8 lg:left-10 lg:top-10">
                            <a href="{{ url('/') }}" class="inline-flex items-center gap-4 rounded-[28px] border border-white/14 bg-white/10 px-5 py-4 backdrop-blur">
                                <img src="{{ $logoBps }}" alt="Logo BPS" class="h-14 w-14 rounded-2xl bg-white object-contain p-1.5">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-cyan-200">SOPERA</p>
                                    <p class="mt-1 text-xl font-bold">SOP Generator</p>
                                    <p class="text-sm text-blue-100/80">BPS Kabupaten Gorontalo Utara</p>
                                </div>
                            </a>
                        </div>

                        <div class="relative grid min-h-[640px] items-center gap-8 pt-28 lg:min-h-[680px] lg:grid-cols-[1.1fr_0.72fr] lg:gap-10 lg:pt-0">
                            <div class="flex h-full flex-col">
                                <div class="mt-auto">
                                    <h1 class="max-w-2xl text-4xl font-bold leading-tight sm:text-5xl">Selamat datang di SOPERA, SOP Generator</h1>
                                    <p class="mt-5 max-w-xl text-base leading-8 text-blue-50/85">
                                        Kelola dokumen SOP, riwayat revisi, template, dan arsip kerja dalam satu sistem yang dirancang modern untuk lingkungan kerja BPS Gorontalo Utara.
                                    </p>

                                    <div class="mt-8 grid gap-4 sm:grid-cols-3">
                                        <div class="rounded-[28px] border border-white/12 bg-white/10 p-4 backdrop-blur">
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-cyan-100">Terintegrasi</p>
                                            <p class="mt-2 text-sm leading-6 text-blue-50/85">Dokumen, arsip, revisi, dan template berada dalam satu alur kerja.</p>
                                        </div>
                                        <div class="rounded-[28px] border border-white/12 bg-white/10 p-4 backdrop-blur">
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-cyan-100">Terkelola</p>
                                            <p class="mt-2 text-sm leading-6 text-blue-50/85">Akses pengguna, aktivitas tim, dan status dokumen lebih mudah dipantau.</p>
                                        </div>
                                        <div class="rounded-[28px] border border-white/12 bg-white/10 p-4 backdrop-blur">
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-cyan-100">Profesional</p>
                                            <p class="mt-2 text-sm leading-6 text-blue-50/85">Desain visual selaras dengan identitas SOPERA dan tema laut yang elegan.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-center lg:h-full lg:justify-end">
                                <div class="w-full max-w-[520px] overflow-hidden rounded-[36px] border border-white/75 bg-white shadow-[0_40px_110px_-55px_rgba(15,23,42,0.45)]">
                                    <div class="border-b border-slate-200/70 bg-[linear-gradient(180deg,rgba(248,251,255,0.98)_0%,rgba(239,246,255,0.92)_100%)] px-6 py-5 sm:px-8 lg:hidden">
                                        <div class="flex items-center gap-4">
                                            <img src="{{ $logoBps }}" alt="Logo BPS" class="h-14 w-14 rounded-2xl bg-white object-contain p-1.5 shadow-sm">
                                            <div>
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.34em] text-blue-700">SOPERA</p>
                                                <p class="text-lg font-bold text-slate-900">SOP Generator</p>
                                                <p class="text-sm text-slate-500">BPS Kabupaten Gorontalo Utara</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-6 py-6 text-slate-900 sm:px-8 sm:py-8">
                                        {{ $slot }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </body>
</html>
