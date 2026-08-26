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
@endsection
