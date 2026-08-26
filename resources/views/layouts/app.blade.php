<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($pageTitle) ? $pageTitle . ' - SOPERA (SOP Generator)' : 'SOPERA (SOP Generator)' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-800">
        <div x-data="{ sidebarOpen: false }" class="relative min-h-screen overflow-hidden bg-[linear-gradient(180deg,#d6e0ee_0%,#e0e8f1_34%,#dae3ee_100%)]">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-80 bg-[radial-gradient(circle_at_top,rgba(30,64,175,0.12),transparent_58%)]"></div>
            <div class="pointer-events-none absolute left-[-8rem] top-16 h-72 w-72 rounded-full bg-cyan-300/18 blur-3xl"></div>
            <div class="pointer-events-none absolute right-[-8rem] top-40 h-80 w-80 rounded-full bg-blue-400/14 blur-3xl"></div>
            <div class="pointer-events-none absolute bottom-[-8rem] left-1/3 h-72 w-72 rounded-full bg-slate-400/16 blur-3xl"></div>

            @include('layouts.navigation')

            <div class="relative lg:pl-[25.5rem]">
                <main class="min-h-screen px-4 pb-8 pt-24 sm:px-6 lg:px-8 lg:pb-10 lg:pr-10 lg:pt-[9.75rem]">
                    @isset($header)
                        <header class="mb-6 overflow-hidden rounded-[30px] border border-white/75 bg-white/84 p-6 shadow-[0_25px_80px_-38px_rgba(37,99,235,0.25)] backdrop-blur-2xl">
                            {{ $header }}
                        </header>
                    @else
                        @hasSection('header')
                            <header class="mb-6 overflow-hidden rounded-[30px] border border-white/75 bg-white/84 p-6 shadow-[0_25px_80px_-38px_rgba(37,99,235,0.25)] backdrop-blur-2xl">
                                @yield('header')
                            </header>
                        @endif
                    @endisset

                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </main>
            </div>
        </div>
    </body>
</html>
