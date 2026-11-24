{{-- Schermata principale del profilo utente che aggrega tutte le sezioni di impostazione personale --}}
<x-app-layout title="Profilo Personale">
    <div class="card shadow-sm border-0 mb-4 overflow-hidden">
        <div class="p-4 bg-white">
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <x-avatar :user="auth()->user()" size="lg" />
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h1 class="h4 fw-bold text-dark mb-0">{{ auth()->user()->full_name }}</h1>
                            @include('partials.role-badges', ['user' => auth()->user()])
                            @foreach (auth()->user()->teams as $team)
                                <span class="badge rounded-pill bg-light text-secondary border fw-medium" title="Team aziendale">
                                    <i class="bi bi-people me-1"></i>{{ $team->name }}
                                </span>
                            @endforeach
                        </div>
                        <p class="text-secondary small mb-0 mt-1">
                            <i class="bi bi-envelope me-1"></i>{{ auth()->user()->email }}
                            <span class="mx-2">·</span>
                            <i class="bi bi-calendar3 me-1"></i>Iscritto da {{ auth()->user()->created_at?->locale('it')->translatedFormat('F Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column gap-4">
        @if (Laravel\Fortify\Features::canUpdateProfileInformation())
            @livewire('profile.update-profile-information-form')
        @endif

        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
            @livewire('profile.update-password-form')
        @endif

        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            @livewire('profile.two-factor-authentication-form')
        @endif

        @livewire('profile.logout-other-browser-sessions-form')
    </div>
</x-app-layout>
