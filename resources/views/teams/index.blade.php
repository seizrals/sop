@extends('layouts.app')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-[28px] border border-emerald-200 bg-emerald-50 px-6 py-4 shadow-[0_20px_60px_-35px_rgba(16,185,129,0.35)] backdrop-blur">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-9 w-9 flex-none items-center justify-center rounded-full bg-emerald-500 text-white">
                    <i class="fa-solid fa-check"></i>
                </span>
                <div>
                    <p class="text-sm font-semibold text-emerald-900">Berhasil</p>
                    <p class="mt-1 text-sm text-emerald-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-[28px] border border-rose-200 bg-rose-50 px-6 py-4 shadow-[0_20px_60px_-35px_rgba(244,63,94,0.35)] backdrop-blur">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-9 w-9 flex-none items-center justify-center rounded-full bg-rose-500 text-white">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
                <div>
                    <p class="text-sm font-semibold text-rose-900">Gagal</p>
                    <p class="mt-1 text-sm text-rose-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[1.7fr_1fr]">
        <section class="overflow-hidden rounded-[32px] border border-white/70 bg-white/85 p-6 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-700">Kelola Tim</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">Daftar Tim Pengelola SOP</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Kelola nama tim, keterangan, dan data tim pengelola dokumen SOP BPS Kabupaten Gorontalo Utara.</p>
                </div>
                <div class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-blue-700 backdrop-blur">
                    <i class="fa-solid fa-water mr-2"></i>
                    {{ $teams->count() }} Tim
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-[28px] border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/90">
                        <tr>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500">Nama Tim</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500">Kode</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500">Pengguna</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500">Kegiatan</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500">SOP</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500">Status</th>
                            <th class="px-5 py-4 text-right font-semibold text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($teams as $team)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $team->display_name }}</p>
                                    @if ($team->description)
                                        <p class="mt-1 text-xs text-slate-500 line-clamp-1">{{ $team->description }}</p>
                                    @endif
                                    @if ($team->leader_name)
                                        <p class="mt-1 text-[11px] font-medium text-blue-600">
                                            <i class="fa-solid fa-user-tie mr-1"></i>{{ $team->leader_name }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <code class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $team->code }}</code>
                                </td>
                                <td class="px-5 py-4 text-slate-500">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                        'bg-slate-100 text-slate-600' => $team->users_count === 0,
                                        'bg-amber-50 text-amber-700' => $team->users_count > 0,
                                    ])>
                                        {{ $team->users_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-500">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                        'bg-slate-100 text-slate-600' => $team->activities_count === 0,
                                        'bg-blue-50 text-blue-700' => $team->activities_count > 0,
                                    ])>
                                        {{ $team->activities_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-500">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                        'bg-slate-100 text-slate-600' => $team->sops_count === 0,
                                        'bg-emerald-50 text-emerald-700' => $team->sops_count > 0,
                                    ])>
                                        {{ $team->sops_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full border {{ $team->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-600' }} px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]">
                                        {{ $team->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            @click="$dispatch('open-modal', 'edit-team-{{ $team->id }}')"
                                            class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100"
                                        >
                                            <i class="fa-solid fa-pen-to-square"></i>
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('teams.destroy', $team) }}" onsubmit="return confirm('Yakin ingin menghapus tim {{ $team->display_name }}? Data yang sudah terhubung (pengguna, kegiatan, SOP) akan mencegah penghapusan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100"
                                            >
                                                <i class="fa-solid fa-trash-can"></i>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-14 text-center text-slate-500">
                                    <div class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-blue-500">
                                        <i class="fa-solid fa-water text-xl"></i>
                                    </div>
                                    <p class="mt-4 text-base font-semibold text-slate-800">Belum ada tim</p>
                                    <p class="mt-2 text-sm">Silakan tambahkan tim baru melalui form di samping.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-[32px] border border-white/70 bg-white/85 p-6 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
            <h3 class="text-xl font-bold text-slate-900">Tambah Tim Baru</h3>
            <p class="mt-2 text-sm leading-6 text-slate-500">Isi formulir di bawah untuk menambahkan tim pengelola SOP baru.</p>

            <form method="POST" action="{{ route('teams.store') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="code">Kode Tim (slug)</label>
                    <input id="code" name="code" value="{{ old('code') }}" placeholder="contoh: ipds, produksi, sosial" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                    @error('code')
                        <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="name">Nama Internal</label>
                    <input id="name" name="name" value="{{ old('name') }}" placeholder="contoh: ipds" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                    @error('name')
                        <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="display_name">Nama Tampilan Tim</label>
                    <input id="display_name" name="display_name" value="{{ old('display_name') }}" placeholder="contoh: IPDS, Statistik Produksi" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                    <p class="mt-1.5 text-xs text-slate-400">Nama ini yang akan ditampilkan di halaman pemilihan tim (menu SOP).</p>
                    @error('display_name')
                        <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="leader_name">Nama Ketua Tim (opsional)</label>
                    <input id="leader_name" name="leader_name" value="{{ old('leader_name') }}" placeholder="contoh: Dr. Irwan S.ST., M.Si." class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                    @error('leader_name')
                        <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="description">Keterangan Tim</label>
                    <textarea id="description" name="description" rows="3" placeholder="Deskripsikan ruang lingkup atau tujuan tim ini..." class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">Tim Aktif</p>
                        <p class="text-xs text-slate-500">Nonaktifkan jika tim tidak lagi digunakan.</p>
                    </div>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" name="is_active" class="peer sr-only" checked>
                        <div class="h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-500 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100"></div>
                    </label>
                </div>
                <button class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" type="submit">
                    <i class="fa-solid fa-plus mr-2"></i>
                    Simpan Tim Baru
                </button>
            </form>
        </section>
    </div>

    @foreach ($teams as $team)
        <x-modal name="edit-team-{{ $team->id }}" maxWidth="lg" focusable>
            <div class="p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-700">Edit Tim</p>
                        <h3 class="mt-2 text-xl font-bold text-slate-900">Ubah Data Tim {{ $team->display_name }}</h3>
                    </div>
                    <button
                        type="button"
                        @click="$dispatch('close-modal', 'edit-team-{{ $team->id }}')"
                        class="inline-flex h-10 w-10 flex-none items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-700"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('teams.update', $team) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="edit-code-{{ $team->id }}">Kode Tim (slug)</label>
                            <input id="edit-code-{{ $team->id }}" name="code" value="{{ old('code', $team->code) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                            @error('code')
                                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="edit-name-{{ $team->id }}">Nama Internal</label>
                            <input id="edit-name-{{ $team->id }}" name="name" value="{{ old('name', $team->name) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                            @error('name')
                                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="edit-display-name-{{ $team->id }}">Nama Tampilan Tim</label>
                        <input id="edit-display-name-{{ $team->id }}" name="display_name" value="{{ old('display_name', $team->display_name) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                        <p class="mt-1.5 text-xs text-slate-400">Nama ini yang akan ditampilkan di halaman pemilihan tim (menu SOP).</p>
                        @error('display_name')
                            <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="edit-leader-name-{{ $team->id }}">Nama Ketua Tim (opsional)</label>
                        <input id="edit-leader-name-{{ $team->id }}" name="leader_name" value="{{ old('leader_name', $team->leader_name) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                        @error('leader_name')
                            <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="edit-description-{{ $team->id }}">Keterangan Tim</label>
                        <textarea id="edit-description-{{ $team->id }}" name="description" rows="3" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">{{ old('description', $team->description) }}</textarea>
                        @error('description')
                            <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-700">Tim Aktif</p>
                            <p class="text-xs text-slate-500">Nonaktifkan jika tim tidak lagi digunakan.</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" name="is_active" class="peer sr-only" {{ old('is_active', $team->is_active) ? 'checked' : '' }}>
                            <div class="h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-500 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100"></div>
                        </label>
                    </div>
                    <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            @click="$dispatch('close-modal', 'edit-team-{{ $team->id }}')"
                            class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto"
                        >
                            Batal
                        </button>
                        <button class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 sm:w-auto" type="submit">
                            <i class="fa-solid fa-floppy-disk mr-2"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </x-modal>
    @endforeach
@endsection
