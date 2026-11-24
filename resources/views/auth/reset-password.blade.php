{{-- Schermata per la reimpostazione della password o prima configurazione da email di benvenuto --}}
@php $welcome ??= false; @endphp

<x-guest-layout>
    <x-authentication-card>
        <div class="mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 48px; height: 48px;">
                <i class="bi bi-shield-lock fs-4"></i>
            </div>
            <h1 class="h3 fw-bold text-dark mb-2">{{ $welcome ? 'Benvenuto!' : 'Nuova password' }}</h1>
            <p class="text-body-secondary small mb-0">{{ $welcome ? "Scegli la password sicura con cui accederai a FlowDesk d'ora in poi." : 'Crea una password robusta per il tuo account.' }}</p>
        </div>

        <form method="POST" action="{{ route($welcome ? 'password.set.store' : 'password.update') }}" class="d-flex flex-column gap-3">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <x-label for="email" value="Email" class="fw-medium mb-1" />
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-envelope"></i></span>
                    <x-input id="email" type="email" name="email" :value="old('email', $request->email)" class="border-start-0 bg-light" required readonly autocomplete="username" />
                </div>
                <x-input-error for="email" class="mt-1" />
            </div>

            <div>
                <x-label for="password" :value="$welcome ? 'Scegli una password' : 'Nuova password'" class="fw-medium mb-1" />
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-lock"></i></span>
                    <x-input id="password" type="password" name="password" placeholder="Minimo 8 caratteri" class="border-start-0" required autofocus autocomplete="new-password" />
                </div>
                <x-input-error for="password" class="mt-1" />
            </div>

            <div>
                <x-label for="password_confirmation" value="Conferma password" class="fw-medium mb-1" />
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-lock-fill"></i></span>
                    <x-input id="password_confirmation" type="password" name="password_confirmation" placeholder="Ripeti la password" class="border-start-0" required autocomplete="new-password" />
                </div>
            </div>

            <x-button class="w-100 py-2 fs-6 fw-semibold shadow-sm mt-2">
                Salva password e continua <i class="bi bi-arrow-right ms-1"></i>
            </x-button>
        </form>
    </x-authentication-card>
</x-guest-layout>
