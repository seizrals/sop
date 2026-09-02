@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="grid gap-6 xl:grid-cols-2">
            <section class="overflow-hidden rounded-[34px] border border-white/70 bg-white/92 p-6 shadow-[0_30px_90px_-45px_rgba(15,23,42,0.18)] backdrop-blur-2xl">
                @include('profile.partials.update-profile-information-form')
            </section>

            <section class="overflow-hidden rounded-[34px] border border-white/70 bg-white/92 p-6 shadow-[0_30px_90px_-45px_rgba(15,23,42,0.18)] backdrop-blur-2xl">
                @include('profile.partials.update-password-form')
            </section>
        </div>

        <section class="overflow-hidden rounded-[34px] border border-white/70 bg-white/92 p-6 shadow-[0_30px_90px_-45px_rgba(15,23,42,0.18)] backdrop-blur-2xl">
            @include('profile.partials.delete-user-form')
        </section>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
            @csrf
            @method('delete')

            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 flex-none items-center justify-center rounded-full bg-rose-50 text-rose-600 ring-8 ring-rose-50/70">
                    <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 9v4"></path>
                        <path d="M12 17h.01"></path>
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-xl font-bold text-slate-900">
                        {{ __('Are you sure you want to delete your account?') }}
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                    </p>
                </div>
            </div>

            <div class="mt-6">
                <label for="password" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{{ __('Password') }}</label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-rose-400 focus:ring-4 focus:ring-rose-100"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-xs text-rose-600" />
            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    x-on:click="$dispatch('close-modal', 'confirm-user-deletion')"
                    class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto"
                >
                    {{ __('Cancel') }}
                </button>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-rose-600 px-5 py-3 text-sm font-semibold text-white shadow-[0_10px_30px_-12px_rgba(220,38,38,0.6)] transition hover:bg-rose-700 focus:outline-none focus:ring-4 focus:ring-rose-100 sm:w-auto">
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
@endsection
