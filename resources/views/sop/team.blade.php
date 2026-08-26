@extends('layouts.app')

@php
    $tableActivities = $activities->map(fn ($activity) => [
        'model' => $activity,
        'name' => $activity->name,
        'description' => $activity->description,
        'sop_documents_count' => $activity->sop_documents_count,
    ]);
@endphp

@section('content')
    <div class="grid gap-6 xl:grid-cols-[1.7fr_1fr]">
        <section class="overflow-hidden rounded-[32px] border border-white/70 bg-white/85 p-6 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-700">Daftar Kegiatan</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ $team->display_name }}</h3>
                    <p class="mt-2 text-sm text-slate-500">Pilih kegiatan untuk masuk ke daftar SOP dan tombol `Buat SOP` sesuai alur yang Anda minta.</p>
                </div>
                <a href="{{ route('sop.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                    Kembali ke Tim
                </a>
            </div>

            @if ($tableActivities->isEmpty())
                <div class="mt-6 rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                    <div class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-blue-500">
                        <i class="fa-solid fa-list-ul text-xl"></i>
                    </div>
                    <h4 class="mt-4 text-lg font-bold text-slate-900">Belum ada kegiatan untuk tim ini</h4>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Tambahkan kegiatan baru melalui form di sebelah kanan untuk mulai membuat daftar SOP.</p>
                </div>
            @else
                <div class="mt-6 overflow-hidden rounded-[28px] border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50/90">
                            <tr>
                                <th class="px-5 py-4 text-left font-semibold text-slate-500">Kegiatan</th>
                                <th class="px-5 py-4 text-left font-semibold text-slate-500">Jumlah SOP</th>
                                <th class="px-5 py-4 text-right font-semibold text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($tableActivities as $activity)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-4">
                                        <div class="max-w-xl">
                                            <p class="font-semibold text-slate-800">{{ $activity['name'] }}</p>
                                            @if ($activity['description'])
                                                <p class="mt-1 text-xs leading-5 text-slate-500">{{ \Illuminate\Support\Str::limit($activity['description'], 120) }}</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-500">{{ $activity['sop_documents_count'] }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('sop.activity', [$team, $activity['model']]) }}" class="inline-flex items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100">
                                            Masuk
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="overflow-hidden rounded-[32px] border border-white/70 bg-white/85 p-6 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
            <h3 class="text-xl font-bold text-slate-900">Tambah Kegiatan</h3>
            <p class="mt-2 text-sm leading-6 text-slate-500">Tambahkan kegiatan baru untuk tim ini. Kegiatan akan langsung tersimpan ke database dan muncul dalam daftar.</p>

            <form method="POST" action="{{ route('sop.activity.store', $team) }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="name">Nama Kegiatan</label>
                    <input id="name" name="name" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none ring-0 transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" placeholder="Contoh: Statistik Air Bersih Tahunan" />
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="min-h-32 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none ring-0 transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" placeholder="Keterangan singkat kegiatan"></textarea>
                </div>
                <button class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" type="submit">Tambah Kegiatan</button>
            </form>
        </section>
    </div>
@endsection
