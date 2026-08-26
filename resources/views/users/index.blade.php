@extends('layouts.app')

@php
    $previewUsers = collect([
        ['name' => 'Admin SOP', 'position' => 'Administrator Sistem', 'email' => 'admin@sop-bps.local', 'team' => '-', 'role' => 'ADMIN', 'active' => true],
        ['name' => 'Ketua Tim Produksi', 'position' => 'Ketua Tim Statistik Produksi', 'email' => 'ketua.produksi@sop-bps.local', 'team' => 'Produksi', 'role' => 'KETUA TIM', 'active' => true],
        ['name' => 'Anggota Tim Social', 'position' => 'Statistisi Ahli Muda', 'email' => 'anggota.social@sop-bps.local', 'team' => 'Social', 'role' => 'ANGGOTA TIM', 'active' => true],
        ['name' => 'Kepala BPS', 'position' => 'Kepala BPS Kabupaten Gorontalo Utara', 'email' => 'kepala@sop-bps.local', 'team' => '-', 'role' => 'KEPALA', 'active' => true],
    ]);
@endphp

@section('content')
    <div class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
        <section class="overflow-hidden rounded-[32px] border border-white/70 bg-white/85 p-6 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-700">Manajemen Pengguna</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">Daftar pengguna</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Tabel pengguna sementara memakai dummy data tetap agar tampilan level dan status bisa ditinjau lebih dulu.</p>
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-[28px] border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/90">
                        <tr>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500">Nama</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500">Email</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500">Tim</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500">Level</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($previewUsers as $user)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $user['name'] }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $user['position'] }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-500">{{ $user['email'] }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ $user['team'] }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ $user['role'] }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full border {{ $user['active'] ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-700' }} px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]">
                                        {{ $user['active'] ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
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
                    <input id="name" name="name" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="email">Email</label>
                    <input id="email" name="email" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" type="email">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="nip">NIP</label>
                    <input id="nip" name="nip" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="position">Jabatan</label>
                    <input id="position" name="position" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="team_id">Tim</label>
                    <select id="team_id" name="team_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                        <option value="">Tanpa tim</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="role">Level</label>
                    <select id="role" name="role" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                        <option value="admin">Admin</option>
                        <option value="ketua_tim">Ketua Tim</option>
                        <option value="anggota_tim">Anggota Tim</option>
                        <option value="kepala">Kepala</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="password">Password</label>
                    <input id="password" name="password" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" type="password">
                </div>
                <button class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" type="submit">Simpan Pengguna</button>
            </form>
        </section>
    </div>
@endsection
