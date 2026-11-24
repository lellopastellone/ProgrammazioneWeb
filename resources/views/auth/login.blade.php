{{-- Schermata di autenticazione con email, password e opzione 'Resta connesso' --}}
<x-guest-layout>
    <x-authentication-card>
        <div class="mb-4">
            <h1 class="h3 fw-bold text-dark mb-2">Accedi al tuo account</h1>
            <p class="text-body-secondary small mb-0">Bentornato! Inserisci le tue credenziali per continuare.</p>
        </div>

        @session('status')
            <div class="alert alert-success d-flex align-items-center gap-2 small mb-3">
                <i class="bi bi-check-circle-fill"></i>
                <div>{{ $value }}</div>
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}" class="d-flex flex-column gap-3">
            @csrf

            <div>
                <x-label for="email" value="Email aziendale" class="fw-medium mb-1" />
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-envelope"></i></span>
                    <x-input id="email" type="email" name="email" :value="old('email')" placeholder="nome@azienda.it" class="border-start-0" required autofocus autocomplete="username" />
                </div>
                <x-input-error for="email" class="mt-1" />
            </div>

            <div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <x-label for="password" value="Password" class="fw-medium mb-0" />
                    <a class="small text-primary text-decoration-none fw-medium" href="{{ route('password.request') }}">Password dimenticata?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-lock"></i></span>
                    <x-input id="password" type="password" name="password" placeholder="••••••••" class="border-start-0" required autocomplete="current-password" />
                </div>
                <x-input-error for="password" class="mt-1" />
            </div>

            <div class="d-flex align-items-center justify-content-between pt-1">
                <div class="form-check form-switch">
                    <x-checkbox id="remember_me" name="remember" />
                    <label class="form-check-label small text-body-secondary" for="remember_me">Resta connesso</label>
                </div>
            </div>

            <x-button class="w-100 py-2 fs-6 fw-semibold shadow-sm mt-2">
                Accedi <i class="bi bi-arrow-right ms-1"></i>
            </x-button>
        </form>
    </x-authentication-card>
</x-guest-layout>
