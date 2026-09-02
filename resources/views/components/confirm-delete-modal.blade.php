<div
    x-data="{
        show: false,
        title: 'Konfirmasi Hapus',
        message: 'Apakah Anda yakin ingin menghapus data ini?',
        pendingForm: null,

        open(payload) {
            if (payload) {
                if (payload.title) this.title = payload.title;
                if (payload.message) this.message = payload.message;
                if (payload.form) this.pendingForm = payload.form;
            }
            this.show = true;
        },

        close() {
            this.show = false;
        },

        confirmDelete() {
            if (this.pendingForm) {
                const form = this.pendingForm;
                this.pendingForm = null;
                this.show = false;
                form.submit();
            }
        }
    }"
    x-init="
        $watch('show', value => {
            if (value) {
                document.body.classList.add('overflow-y-hidden');
            } else {
                document.body.classList.remove('overflow-y-hidden');
                pendingForm = null;
            }
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && show) close();
        });
    "
    x-on:open-modal.window="if ($event.detail == 'confirm-delete-modal') open()"
    x-on:confirm-delete.window="open($event.detail)"
    x-on:close-modal.window="if ($event.detail == 'confirm-delete-modal') close()"
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
        class="relative z-10 mb-0 w-full sm:w-full sm:max-w-md sm:mx-auto overflow-hidden rounded-[32px] border border-white/70 bg-white shadow-[0_35px_100px_-35px_rgba(15,23,42,0.55)] transform transition-all"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-delete-title"
    >
        <div class="p-6 sm:p-8">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 flex-none items-center justify-center rounded-full bg-red-50 text-red-600 ring-8 ring-red-50/70">
                    <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 9v4"></path>
                        <path d="M12 17h.01"></path>
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 id="confirm-delete-title" class="text-xl font-bold text-slate-900" x-text="title">Konfirmasi Hapus</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600" x-text="message">Apakah Anda yakin ingin menghapus data ini?</p>
                </div>
            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    x-on:click="close()"
                    class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto"
                >
                    Batal
                </button>
                <button
                    type="button"
                    x-on:click="confirmDelete()"
                    class="inline-flex w-full items-center justify-center rounded-2xl bg-red-600 px-5 py-3 text-sm font-semibold text-white shadow-[0_10px_30px_-12px_rgba(220,38,38,0.6)] transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100 sm:w-auto"
                >
                    <svg viewBox="0 0 24 24" class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18"></path>
                        <path d="M8 6V4h8v2"></path>
                        <path d="M19 6l-1 14H6L5 6"></path>
                    </svg>
                    Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (form && form.matches('[data-delete-confirm]')) {
            e.preventDefault();
            window.dispatchEvent(new CustomEvent('confirm-delete', {
                detail: {
                    title: form.dataset.deleteTitle || 'Konfirmasi Hapus',
                    message: form.dataset.deleteMessage || 'Apakah Anda yakin ingin menghapus data ini?',
                    form: form
                }
            }));
        }
    }, true);
});
</script>
