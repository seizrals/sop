<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center">
            <p class="text-xl font-bold tracking-tight text-slate-900">Silakan Masuk</p>
            <p class="mt-3 text-sm leading-7 text-slate-500">
                Masukkan email dan kata sandi Anda untuk mengakses dashboard, dokumen SOP, arsip, dan riwayat revisi.
            </p>
        </div>

        <x-auth-session-status
            class="rounded-[22px] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
            :status="session('status')"
        />

        @if ($errors->any())
            <div class="rounded-[22px] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-100 text-xs text-rose-600">
                        <i class="fa-solid fa-exclamation"></i>
                    </span>
                    <div>
                        <p class="font-semibold">Login belum berhasil</p>
                        <p class="mt-1 leading-6">{{ $errors->first() }}</p>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-regular fa-envelope"></i>
                    </span>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="h-14 w-full rounded-2xl border border-slate-200 bg-white/90 pl-12 pr-4 text-sm text-slate-800 shadow-[0_10px_30px_-24px_rgba(15,23,42,0.24)] outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                        placeholder="nama@bps.go.id"
                    >
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-rose-600" />
            </div>

            <div>
                <div class="mb-2 flex items-center justify-between gap-3">
                    <label for="password" class="block text-sm font-semibold text-slate-700">Kata sandi</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-700 transition hover:text-blue-800">
                            Lupa kata sandi?
                        </a>
                    @endif
                </div>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="h-14 w-full rounded-2xl border border-slate-200 bg-white/90 pl-12 pr-14 text-sm text-slate-800 shadow-[0_10px_30px_-24px_rgba(15,23,42,0.24)] outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                        placeholder="Masukkan kata sandi"
                    >
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-slate-600"
                        data-password-toggle
                        aria-label="Tampilkan kata sandi"
                    >
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-600" />
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <label for="remember_me" class="inline-flex cursor-pointer items-center gap-3 text-sm text-slate-600">
                    <input
                        id="remember_me"
                        type="checkbox"
                        name="remember"
                        class="h-4 w-4 rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500"
                    >
                    <span>Ingat saya</span>
                </label>
                <div class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">
                    Aman & terenkripsi
                </div>
            </div>

            <button
                type="submit"
                class="inline-flex h-14 w-full items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#081a40_0%,#0d3a8b_55%,#1d74d8_100%)] px-5 text-sm font-semibold text-white shadow-[0_20px_45px_-25px_rgba(13,58,139,0.55)] transition hover:brightness-105"
            >
                Masuk ke Dashboard
            </button>
        </form>

        <div class="rounded-[24px] border border-slate-200 bg-slate-50/90 px-4 py-4 text-sm text-slate-500">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-700">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>
                <div>
                    <p class="font-semibold text-slate-700">Akses hanya untuk pengguna terdaftar</p>
                    <p class="mt-1 leading-6">Jika Anda belum memiliki akun atau akun tidak aktif, silakan hubungi administrator SOPERA.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.querySelector('[data-password-toggle]');
            const passwordInput = document.getElementById('password');

            if (!toggle || !passwordInput) {
                return;
            }

            toggle.addEventListener('click', () => {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggle.innerHTML = isPassword
                    ? '<i class="fa-regular fa-eye-slash"></i>'
                    : '<i class="fa-regular fa-eye"></i>';
            });
        });
    </script>
</x-guest-layout>
