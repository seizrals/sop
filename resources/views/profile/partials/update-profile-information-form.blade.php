<section>
    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-700">Informasi Profil</p>
            <h2 class="mt-2 text-2xl font-bold text-slate-900">
                Perbarui identitas akun
            </h2>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                Ubah nama, email, dan foto profil agar identitas Anda tampil rapi di seluruh sistem.
            </p>
        </div>
        <div class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">
            Profil Aktif
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-8 space-y-6">
        @csrf
        @method('patch')

        <div class="rounded-[28px] border border-slate-200 bg-[linear-gradient(180deg,#f8fbff_0%,#f3f7ff_100%)] p-5">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center">
                <div class="shrink-0">
                    @if ($user->profilePhotoUrl())
                        <img src="{{ $user->profilePhotoUrl() }}" alt="Foto profil {{ $user->name }}" class="h-24 w-24 rounded-[28px] border border-white bg-white object-cover shadow-[0_15px_35px_-28px_rgba(15,23,42,0.28)]">
                    @else
                        <div class="flex h-24 w-24 items-center justify-center rounded-[28px] bg-[linear-gradient(135deg,#0f172a_0%,#2563eb_100%)] text-3xl font-bold text-white shadow-[0_18px_45px_-30px_rgba(37,99,235,0.38)]">
                            {{ $user->initials() }}
                        </div>
                    @endif
                </div>

                <div class="flex-1 space-y-4">
                    <div>
                        <label for="profile_photo" class="mb-2 block text-sm font-semibold text-slate-700">Foto profil</label>
                        <input
                            id="profile_photo"
                            name="profile_photo"
                            type="file"
                            accept=".jpg,.jpeg,.png,.webp"
                            class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-blue-600"
                        >
                        <p class="mt-2 text-xs leading-5 text-slate-500">Format yang didukung: JPG, PNG, atau WEBP. Ukuran maksimal 2MB.</p>
                        <x-input-error class="mt-2 text-xs text-rose-600" :messages="$errors->get('profile_photo')" />
                    </div>

                    @if ($user->profilePhotoUrl())
                        <label class="inline-flex items-center gap-3 text-sm text-slate-600">
                            <input type="checkbox" name="remove_photo" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span>Hapus foto profil saat ini</span>
                        </label>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            <div>
                <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Nama lengkap</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name', $user->name) }}"
                    required
                    autofocus
                    autocomplete="name"
                    class="h-14 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-800 shadow-[0_10px_30px_-24px_rgba(15,23,42,0.18)] outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
                <x-input-error class="mt-2 text-xs text-rose-600" :messages="$errors->get('name')" />
            </div>

            <div>
                <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', $user->email) }}"
                    required
                    autocomplete="username"
                    class="h-14 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-800 shadow-[0_10px_30px_-24px_rgba(15,23,42,0.18)] outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
                <x-input-error class="mt-2 text-xs text-rose-600" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div>
                        <p class="mt-2 text-sm text-slate-700">
                            {{ __('Your email address is unverified.') }}

                            <button form="send-verification" class="underline text-sm text-blue-700 hover:text-blue-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-emerald-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-600">
                Simpan Perubahan
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-emerald-600"
                >Perubahan tersimpan.</p>
            @endif
        </div>
    </form>
</section>
