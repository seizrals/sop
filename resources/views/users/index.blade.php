@extends('layouts.app')

@section('content')
    <div class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
        <section class="overflow-hidden rounded-[32px] border border-white/70 bg-white/85 p-6 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-700">Manajemen Pengguna</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">Daftar pengguna</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Kelola data pengguna, perbarui peran, atur ulang kata sandi, dan sesuaikan keanggotaan tim.</p>
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-[28px] border border-slate-200 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/90">
                        <tr>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500 whitespace-nowrap">Nama</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500 whitespace-nowrap">Email</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500 whitespace-nowrap">Tim</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500 whitespace-nowrap">Level</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500 whitespace-nowrap">Status</th>
                            <th class="px-5 py-4 text-right font-semibold text-slate-500 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($users as $user)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $user->position ?: ($user->nip ? 'NIP ' . $user->nip : '-') }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-500 whitespace-nowrap">{{ $user->email }}</td>
                                <td class="px-5 py-4 text-slate-500 whitespace-nowrap">{{ $user->team?->display_name ?: '-' }}</td>
                                <td class="px-5 py-4 text-slate-500 whitespace-nowrap">
                                    <span class="text-xs font-semibold uppercase tracking-[0.15em]">
                                        {{ match($user->role) {
                                            'admin' => 'Admin',
                                            'ketua_tim' => 'Ketua Tim',
                                            'anggota_tim' => 'Anggota Tim',
                                            'kepala' => 'Kepala',
                                            default => ucfirst($user->role),
                                        } }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex rounded-full border {{ $user->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-700' }} px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        <button
                                            type="button"
                                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-100"
                                            data-user='{!! json_encode([
                                                "id" => $user->id,
                                                "name" => $user->name,
                                                "email" => $user->email,
                                                "nip" => $user->nip,
                                                "position" => $user->position,
                                                "team_id" => $user->team_id,
                                                "role" => $user->role,
                                                "is_active" => $user->is_active,
                                            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}'
                                            x-on:click="window.dispatchEvent(new CustomEvent('open-edit-user', { detail: JSON.parse($el.dataset.user) }))"
                                        >
                                            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 20h9"></path>
                                                <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4Z"></path>
                                            </svg>
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" data-delete-confirm data-delete-title="Hapus Pengguna" data-delete-message="{{ $user->id === auth()->id() ? 'Anda tidak dapat menghapus akun sendiri.' : 'Anda yakin ingin menghapus pengguna ' . e($user->name) . '? Seluruh data yang terkait dengan pengguna ini akan berpengaruh.' }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                                class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-rose-50"
                                            >
                                                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 6h18"></path>
                                                    <path d="M8 6V4h8v2"></path>
                                                    <path d="M19 6l-1 14H6L5 6"></path>
                                                </svg>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        @if ($users->isEmpty())
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada pengguna yang terdaftar.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-[32px] border border-white/70 bg-white/85 p-6 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
            <h3 class="text-xl font-bold text-slate-900">Tambah Pengguna</h3>
            <form method="POST" action="{{ route('users.store') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="name">Nama</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" required>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" required>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="nip">NIP</label>
                    <input id="nip" name="nip" value="{{ old('nip') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="position">Jabatan</label>
                    <input id="position" name="position" value="{{ old('position') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="team_id">Tim</label>
                    <select id="team_id" name="team_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                        <option value="">Tanpa tim</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id') == $team->id)>{{ $team->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="role">Level</label>
                    <select id="role" name="role" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" required>
                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                        <option value="ketua_tim" @selected(old('role') === 'ketua_tim')>Ketua Tim</option>
                        <option value="anggota_tim" @selected(old('role') === 'anggota_tim')>Anggota Tim</option>
                        <option value="kepala" @selected(old('role') === 'kepala')>Kepala</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="password">Password</label>
                    <input id="password" name="password" type="password" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" required minlength="8">
                </div>
                <button class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" type="submit">Simpan Pengguna</button>
            </form>
        </section>
    </div>

    <div
        x-data="{
            show: false,
            loading: false,
            form: { id: null, name: '', email: '', nip: '', position: '', team_id: '', role: 'anggota_tim', is_active: true, password: '' },

            open(payload) {
                this.form = {
                    id: payload.id ?? null,
                    name: payload.name ?? '',
                    email: payload.email ?? '',
                    nip: payload.nip ?? '',
                    position: payload.position ?? '',
                    team_id: payload.team_id ?? '',
                    role: payload.role ?? 'anggota_tim',
                    is_active: payload.is_active ?? true,
                    password: '',
                };
                this.show = true;
            },

            close() {
                if (this.loading) return;
                this.show = false;
            },

            submitForm() {
                const formEl = document.getElementById('edit-user-form');
                if (!formEl) return;
                const actionUrl = formEl.action;
                const formData = new FormData(formEl);
                formData.append('_method', 'PATCH');

                this.loading = true;

                fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json, text/html'
                    },
                    credentials: 'same-origin',
                    body: formData
                }).then(response => {
                    if (response.redirected) {
                        window.location.href = response.url;
                    } else {
                        window.location.reload();
                    }
                }).catch(() => {
                    window.location.reload();
                }).finally(() => {
                    this.loading = false;
                });
            }
        }"
        x-init="
            window.addEventListener('open-edit-user', e => open(e.detail));
            $watch('show', value => {
                if (value) {
                    document.body.classList.add('overflow-y-hidden');
                } else {
                    document.body.classList.remove('overflow-y-hidden');
                }
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && show && !loading) close();
            });
        "
        x-on:open-modal.window="if ($event.detail == 'edit-user-modal') show = true"
        x-on:close-modal.window="if ($event.detail == 'edit-user-modal') close()"
        x-show="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-0"
        style="display: none;"
    >
        <div
            x-show="show"
            class="fixed inset-0 transform transition-all"
            x-on:click="close()"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        </div>

        <div
            x-show="show"
            class="relative z-10 mb-0 w-full sm:w-full sm:max-w-xl sm:mx-auto overflow-hidden rounded-[32px] border border-white/70 bg-white shadow-[0_35px_100px_-35px_rgba(15,23,42,0.55)] transform transition-all"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            role="dialog"
            aria-modal="true"
            aria-labelledby="edit-user-title"
        >
            <div class="p-6 sm:p-8">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 flex-none items-center justify-center rounded-full bg-blue-50 text-blue-600 ring-8 ring-blue-50/70">
                        <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4Z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 id="edit-user-title" class="text-xl font-bold text-slate-900">Edit Pengguna</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Perbarui data pengguna, ubah keanggotaan tim, atau atur ulang kata sandi jika pengguna lupa.</p>
                    </div>
                </div>

                <form id="edit-user-form"
                      :action="`{{ url('users') }}/${form.id}`"
                      method="POST"
                      x-on:submit.prevent="submitForm()"
                      class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2"
                >
                    @csrf

                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="edit-name">Nama</label>
                        <input id="edit-name" name="name" x-model="form.name" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="edit-email">Email</label>
                        <input id="edit-email" name="email" type="email" x-model="form.email" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="edit-nip">NIP</label>
                        <input id="edit-nip" name="nip" x-model="form.nip" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="edit-position">Jabatan</label>
                        <input id="edit-position" name="position" x-model="form.position" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="edit-team_id">Tim</label>
                        <select id="edit-team_id" name="team_id" x-model="form.team_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                            <option value="">Tanpa tim</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="edit-role">Level</label>
                        <select id="edit-role" name="role" x-model="form.role" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                            <option value="admin">Admin</option>
                            <option value="ketua_tim">Ketua Tim</option>
                            <option value="anggota_tim">Anggota Tim</option>
                            <option value="kepala">Kepala</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Status</label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <input name="is_active" type="checkbox" x-bind:checked="form.is_active" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" value="1">
                            <span class="text-sm font-medium text-slate-700">Aktifkan pengguna ini</span>
                        </label>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="edit-password">
                            Password Baru
                            <span class="ml-1 text-[10px] font-normal normal-case tracking-normal text-slate-400">(kosongkan jika tidak diubah)</span>
                        </label>
                        <input id="edit-password" name="password" type="password" x-model="form.password" minlength="8" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" placeholder="Minimal 8 karakter">
                    </div>

                    <div class="mt-4 sm:col-span-2 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            x-on:click="close()"
                            :disabled="loading"
                            class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto disabled:opacity-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="loading"
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 sm:w-auto disabled:opacity-50"
                        >
                            <svg x-show="loading" viewBox="0 0 24 24" class="mr-2 h-4 w-4 animate-spin" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="loading ? 'Menyimpan...' : 'Simpan Perubahan'">Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
