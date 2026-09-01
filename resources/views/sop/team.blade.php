@extends('layouts.app')

@php
    $authUser = Auth::user();
    $isAdmin = $authUser?->role === 'admin';
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
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('sop.activity', [$team, $activity['model']]) }}" class="inline-flex items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100">
                                                Masuk
                                            </a>
                                            @if ($isAdmin)
                                                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 text-amber-700 transition hover:bg-amber-100" title="Edit Kegiatan" data-activity-edit="{{ $activity['model']->id }}" data-activity-name="{{ $activity['name'] }}" data-activity-description="{{ $activity['description'] ?? '' }}">
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M12 20h9"></path>
                                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"></path>
                                                    </svg>
                                                </button>
                                                <form method="POST" action="{{ route('sop.activity.destroy', [$team, $activity['model']]) }}" class="inline" onsubmit="return confirm('Anda yakin menghapus kegiatan ini? Seluruh SOP di dalamnya juga akan ikut terhapus.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="inline-flex h-9 w-9 items-center justify-center rounded-2xl border border-red-200 bg-red-50 text-red-700 transition hover:bg-red-100" type="submit" title="Hapus Kegiatan (Admin Only)">
                                                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M3 6h18"></path>
                                                            <path d="M8 6V4h8v2"></path>
                                                            <path d="M19 6l-1 14H6L5 6"></path>
                                                            <path d="M10 11v6"></path>
                                                            <path d="M14 11v6"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
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

    @if ($isAdmin)
        <x-modal name="edit-activity-modal" focusable>
            <div class="p-6">
                <h3 class="text-lg font-bold text-slate-900">Edit Kegiatan</h3>
                <form id="edit-activity-form" method="POST" action="" class="mt-4">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="edit-name">Nama Kegiatan</label>
                            <input id="edit-name" name="name" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" placeholder="Nama kegiatan" required />
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="edit-description">Deskripsi</label>
                            <textarea id="edit-description" name="description" class="min-h-32 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" placeholder="Keterangan singkat kegiatan"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100" x-on:click.prevent="$dispatch('close-modal', 'edit-activity-modal')">
                            Batal
                        </button>
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </x-modal>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const editButtons = document.querySelectorAll('[data-activity-edit]');
                const form = document.getElementById('edit-activity-form');
                const nameInput = document.getElementById('edit-name');
                const descInput = document.getElementById('edit-description');

                editButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const activityId = button.dataset.activityEdit;
                        const name = button.dataset.activityName || '';
                        const description = button.dataset.activityDescription || '';

                        const baseUrl = `{{ url('/sop/team/' . $team->id . '/activity') }}/${activityId}`;
                        form.action = baseUrl;
                        nameInput.value = name;
                        descInput.value = description;

                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-activity-modal' }));
                    });
                });
            });
        </script>
    @endif
@endsection
