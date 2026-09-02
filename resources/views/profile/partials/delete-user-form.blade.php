<section class="space-y-6">
    <header>
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-rose-600">Zona Berisiko</p>
        <h2 class="mt-2 text-2xl font-bold text-slate-900">
            Hapus Akun
        </h2>

        <p class="mt-2 text-sm leading-6 text-slate-500">
            Setelah akun Anda dihapus, seluruh sumber dan data yang terkait akan terhapus secara permanen. Sebelum menghapus akun, silakan unduh data atau informasi apa pun yang ingin Anda simpan.
        </p>
    </header>

    <div class="rounded-[28px] border border-rose-200 bg-rose-50/80 p-5">
        <p class="text-sm leading-6 text-rose-700">
            Gunakan aksi ini hanya jika benar-benar diperlukan. Penghapusan akun bersifat permanen dan akan menghapus seluruh data terkait akun ini.
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
</section>
