@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="overflow-hidden rounded-[32px] border border-white/70 bg-white/85 p-6 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
            <form method="GET" class="grid gap-4 md:grid-cols-[1fr_auto]">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.25em] text-slate-500" for="team">Filter Tim</label>
                    <select id="team" name="team" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                        <option value="">Semua tim</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected($selectedTeam === $team->id)>{{ $team->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="inline-flex self-end items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" type="submit">Terapkan Filter</button>
            </form>
        </section>

        <section class="overflow-hidden rounded-[32px] border border-white/70 bg-white/85 p-6 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.24)] backdrop-blur">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-700">Template SOP</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">Daftar Template SOP</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Template di halaman ini dikelola untuk kebutuhan `load template` saat pengguna membuat atau merevisi SOP.</p>
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-[28px] border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/90">
                        <tr>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500">Template</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500">Tim</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500">Kegiatan</th>
                            <th class="px-5 py-4 text-left font-semibold text-slate-500">Sumber SOP</th>
                            <th class="px-5 py-4 text-right font-semibold text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($templates as $template)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $template->name }}</p>
                                    @if ($template->template_code)
                                        <p class="mt-1 text-xs text-slate-500">{{ $template->template_code }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-slate-500">{{ $template->team?->display_name ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ $template->activity?->name ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ $template->sourceSop?->title ?? '-' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <form method="POST" action="{{ route('templates.destroy', $template) }}" data-delete-confirm data-delete-title="Hapus Template SOP" data-delete-message="Anda yakin menghapus template ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center rounded-2xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-16 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[28px] border border-slate-200 bg-slate-50 text-slate-400">
                                        <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"></path>
                                            <path d="M14 2v6h6"></path>
                                            <path d="M9 13h6"></path>
                                            <path d="M9 17h6"></path>
                                        </svg>
                                    </div>
                                    <p class="mt-4 text-base font-semibold text-slate-800">Belum ada template SOP</p>
                                    <p class="mt-1 text-sm text-slate-500">Template baru akan muncul secara otomatis ketika pengguna menyimpan template dari editor SOP.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
