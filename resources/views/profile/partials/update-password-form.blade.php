<section>
    <header>
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-700">Keamanan Akun</p>
        <h2 class="mt-2 text-2xl font-bold text-slate-900">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-2 text-sm leading-6 text-slate-500">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-8 space-y-6">
        @csrf
        @method('put')

        <div class="grid gap-5">
            <div>
                <label for="update_password_current_password" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('Current Password') }}</label>
                <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="h-14 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-800 shadow-[0_10px_30px_-24px_rgba(15,23,42,0.18)] outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-xs text-rose-600" />
            </div>

            <div>
                <label for="update_password_password" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('New Password') }}</label>
                <input id="update_password_password" name="password" type="password" autocomplete="new-password" class="h-14 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-800 shadow-[0_10px_30px_-24px_rgba(15,23,42,0.18)] outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-xs text-rose-600" />
            </div>

            <div>
                <label for="update_password_password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('Confirm Password') }}</label>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="h-14 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-800 shadow-[0_10px_30px_-24px_rgba(15,23,42,0.18)] outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-xs text-rose-600" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-600">
                Simpan Password
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-emerald-600"
                >Password berhasil diperbarui.</p>
            @endif
        </div>
    </form>
</section>
