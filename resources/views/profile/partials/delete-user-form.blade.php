<section class="space-y-6">
    <header>
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-rose-600">Zona Berisiko</p>
        <h2 class="mt-2 text-2xl font-bold text-slate-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-2 text-sm leading-6 text-slate-500">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <div class="rounded-[28px] border border-rose-200 bg-rose-50/80 p-5">
        <p class="text-sm leading-6 text-rose-700">
            Gunakan aksi ini hanya jika benar-benar diperlukan. Penghapusan akun bersifat permanen dan akan menghapus data terkait akun ini.
        </p>

        <button
            type="button"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="mt-5 inline-flex items-center justify-center rounded-2xl bg-rose-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-700"
        >
            Hapus Akun
        </button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('Password') }}</label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-rose-400 focus:ring-4 focus:ring-rose-100"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-xs text-rose-600" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
