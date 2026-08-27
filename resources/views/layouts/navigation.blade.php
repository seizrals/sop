@php
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'fa-solid fa-table-columns'],
        ['label' => 'SOP', 'route' => 'sop.index', 'active' => 'sop.*', 'icon' => 'fa-solid fa-file-circle-plus'],
        ['label' => 'Template', 'route' => 'templates.index', 'active' => 'templates.*', 'icon' => 'fa-regular fa-clone'],
        ['label' => 'Arsip', 'route' => 'archives.index', 'active' => 'archives.*', 'icon' => 'fa-solid fa-box-archive'],
        ['label' => 'Pengguna', 'route' => 'users.index', 'active' => 'users.*', 'icon' => 'fa-solid fa-users'],
    ];
    $logoBps = asset('resources/img/logo-bps.png');
    $lautHero = asset('resources/img/laut-2.png');
    $lautSidebar = asset('resources/img/laut-1.png');
    $todayLabel = \Carbon\Carbon::now()->translatedFormat('d F Y');
    $authUser = Auth::user();
@endphp

<div class="lg:hidden">
    <div class="fixed inset-x-0 top-0 z-40 border-b border-white/70 bg-white/90 shadow-[0_18px_45px_-35px_rgba(15,23,42,0.28)] backdrop-blur-2xl">
        <div class="flex items-center justify-between px-4 py-3 sm:px-6">
            <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
                <img src="{{ $logoBps }}" alt="Logo BPS" class="h-10 w-10 rounded-2xl bg-white object-contain p-1 shadow-[0_10px_30px_-18px_rgba(15,23,42,0.55)]">
                <div class="min-w-0">
                    <p class="truncate text-[11px] font-semibold uppercase tracking-[0.32em] text-blue-700">SOPERA</p>
                    <p class="truncate text-sm font-bold text-slate-900">SOP Generator</p>
                </div>
            </a>
            <button
                type="button"
                @click="sidebarOpen = !sidebarOpen"
                class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white p-3 text-slate-600 shadow-[0_18px_45px_-30px_rgba(15,23,42,0.35)] transition hover:bg-slate-50"
            >
                <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <path :class="{ 'hidden': sidebarOpen, 'inline-flex': !sidebarOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
                    <path :class="{ 'hidden': !sidebarOpen, 'inline-flex': sidebarOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>
    </div>

    <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/35" @click="sidebarOpen = false"></div>
    <aside
        x-cloak
        x-show="sidebarOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-4 left-4 z-50 flex w-[18rem] flex-col overflow-hidden rounded-[32px] border border-white/20 bg-[linear-gradient(180deg,rgba(8,26,64,0.94)_0%,rgba(13,47,116,0.88)_58%,rgba(19,84,160,0.72)_100%)] px-4 py-5 text-white shadow-[0_35px_90px_-35px_rgba(6,24,56,0.72)] backdrop-blur-[28px]"
    >
        <div class="absolute inset-0 opacity-[0.14]" style="background-image: url('{{ $lautSidebar }}'); background-size: cover; background-position: center bottom;"></div>
        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(6,24,56,0.55)_0%,rgba(10,41,94,0.2)_42%,rgba(19,84,160,0.08)_100%)]"></div>
        <div class="relative flex items-center gap-3 px-2">
            <img src="{{ $logoBps }}" alt="Logo BPS" class="h-12 w-12 rounded-2xl bg-white object-contain p-1.5">
            <div class="min-w-0">
                <p class="truncate text-[11px] font-semibold uppercase tracking-[0.32em] text-cyan-200">SOPERA</p>
                <p class="truncate text-sm font-bold">SOP Generator</p>
                <p class="truncate text-xs text-slate-300">BPS Gorontalo Utara</p>
            </div>
        </div>

        <nav class="relative mt-8 space-y-2">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    @click="sidebarOpen = false"
                    @class([
                        'flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition',
                        'bg-white text-slate-900 shadow-[0_18px_45px_-30px_rgba(255,255,255,0.45)]' => request()->routeIs($item['active']),
                        'text-slate-300 hover:bg-white/10 hover:text-white' => ! request()->routeIs($item['active']),
                    ])
                >
                    <span @class([
                        'inline-flex h-9 w-9 items-center justify-center rounded-2xl text-sm',
                        'bg-slate-900 text-white' => request()->routeIs($item['active']),
                        'bg-white/10 text-cyan-100' => ! request()->routeIs($item['active']),
                    ])><i class="{{ $item['icon'] }}"></i></span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="relative mt-auto pt-6">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center justify-center gap-3 rounded-2xl border border-rose-400/20 bg-rose-500/10 px-4 py-3 text-sm font-medium text-rose-200 transition hover:bg-rose-500/15 hover:text-rose-100">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </aside>
</div>

<div class="pointer-events-none fixed left-[26rem] right-8 top-8 z-30 hidden lg:block">
    <div class="pointer-events-auto relative overflow-hidden flex items-center justify-between gap-6 rounded-[32px] border border-white/60 bg-[linear-gradient(135deg,rgba(8,26,64,0.9)_0%,rgba(13,47,116,0.84)_52%,rgba(14,89,164,0.78)_100%)] px-6 py-4 shadow-[0_30px_80px_-35px_rgba(8,26,64,0.35)] backdrop-blur-2xl">
        <div class="absolute inset-0 opacity-18" style="background-image: url('{{ $lautHero }}'); background-size: cover; background-position: center;"></div>
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(6,24,56,0.88)_0%,rgba(7,30,72,0.7)_55%,rgba(10,53,120,0.5)_100%)]"></div>
        <div class="min-w-0">
            <p class="relative text-xs font-semibold uppercase tracking-[0.28em] text-cyan-200">SOPERA</p>
            <p class="relative mt-1 text-sm text-blue-50/90">SOP Generator untuk pengelolaan dokumen SOP BPS Gorontalo Utara.</p>
        </div>
        <div class="relative flex items-center gap-3">
            <div class="hidden rounded-2xl border border-white/14 bg-white/12 px-4 py-3 text-right shadow-[0_15px_40px_-28px_rgba(0,0,0,0.24)] backdrop-blur xl:flex xl:items-center xl:gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-cyan-100">
                    <i class="fa-regular fa-calendar"></i>
                </span>
                <div>
                    <p class="text-sm font-semibold text-white">{{ $todayLabel }}</p>
                </div>
            </div>
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-2xl border border-white/14 bg-white/12 px-3 py-2 shadow-[0_18px_45px_-30px_rgba(0,0,0,0.24)] backdrop-blur transition hover:bg-white/18">
                @if ($authUser?->profilePhotoUrl())
                    <img src="{{ $authUser->profilePhotoUrl() }}" alt="Foto profil {{ $authUser->name }}" class="h-11 w-11 rounded-full border border-white/20 object-cover shadow-[0_12px_28px_-18px_rgba(0,0,0,0.35)]">
                @else
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-cyan-400 text-sm font-bold text-white">
                        {{ $authUser?->initials() ?: 'U' }}
                    </span>
                @endif
                <span class="min-w-0">
                    <span class="block truncate text-sm font-semibold text-white">{{ $authUser?->name }}</span>
                    <span class="block truncate text-xs text-blue-100/80">Edit profil</span>
                </span>
            </a>
        </div>
    </div>
</div>

<aside class="fixed bottom-6 left-6 top-6 z-30 hidden w-[21.5rem] overflow-hidden rounded-[34px] border border-white/18 bg-[linear-gradient(180deg,rgba(7,24,59,0.96)_0%,rgba(10,41,94,0.92)_52%,rgba(18,85,157,0.72)_100%)] px-6 py-6 text-white shadow-[0_40px_120px_-45px_rgba(8,24,58,0.72)] backdrop-blur-[28px] lg:flex lg:flex-col">
    <div class="absolute inset-0 opacity-[0.14]" style="background-image: url('{{ $lautSidebar }}'); background-size: cover; background-position: center bottom;"></div>
    <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(6,24,56,0.58)_0%,rgba(10,41,94,0.2)_42%,rgba(19,84,160,0.08)_100%)]"></div>
    <div class="absolute inset-0 rounded-[34px] border border-white/12"></div>
    <div class="relative">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-4 rounded-[28px] border border-white/12 bg-white/8 p-4 shadow-[0_18px_45px_-30px_rgba(0,0,0,0.22)] backdrop-blur">
        <img src="{{ $logoBps }}" alt="Logo BPS" class="h-14 w-14 rounded-2xl bg-white object-contain p-1.5">
        <div class="min-w-0">
            <p class="truncate text-[11px] font-semibold uppercase tracking-[0.34em] text-cyan-200">SOPERA</p>
            <p class="truncate text-base font-bold text-white">SOP Generator</p>
            <p class="truncate text-xs text-slate-300">BPS Gorontalo Utara</p>
        </div>
    </a>

    <div class="mt-8 px-1">
        <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-slate-300/90">Navigasi</p>
        <nav class="mt-3 space-y-2">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    @class([
                        'flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition',
                        'bg-white/16 text-white shadow-[0_18px_45px_-30px_rgba(0,0,0,0.28)] backdrop-blur' => request()->routeIs($item['active']),
                        'text-slate-200 hover:bg-white/10 hover:text-white' => ! request()->routeIs($item['active']),
                    ])
                >
                    <span @class([
                        'inline-flex h-10 w-10 items-center justify-center rounded-2xl text-sm',
                        'bg-white/14 text-white' => request()->routeIs($item['active']),
                        'bg-white/10 text-cyan-100' => ! request()->routeIs($item['active']),
                    ])><i class="{{ $item['icon'] }}"></i></span>
                    <div class="min-w-0">
                        <p class="truncate">{{ $item['label'] }}</p>
                        <p @class([
                            'truncate text-xs',
                            'text-slate-300' => request()->routeIs($item['active']),
                            'text-slate-300/80' => ! request()->routeIs($item['active']),
                        ])>
                            {{ $item['label'] === 'Dashboard' ? 'Ringkasan sistem' : 'Kelola menu ' . strtolower($item['label']) }}
                        </p>
                    </div>
                </a>
            @endforeach
        </nav>
    </div>

    <div class="mt-auto pt-6">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="inline-flex w-full items-center justify-center gap-3 rounded-2xl border border-white/12 bg-white/8 px-4 py-3 text-sm font-medium text-rose-100 transition hover:bg-rose-500/14 hover:text-white">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
    </div>
</aside>
