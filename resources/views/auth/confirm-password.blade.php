{{-- Schermata a pagina intera per la riconferma della password prima di accedere ad aree protette --}}
<x-guest-layout>
    <x-authentication-card>
        <div class="mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle mb-3" style="width: 48px; height: 48px;">
                <i class="bi bi-shield-lock-fill fs-4"></i>
            </div>
            <h1 class="h3 fw-bold text-dark mb-2">Conferma password</h1>
            <p class="text-body-secondary small mb-0">Questa è un'area protetta dell'applicazione. Conferma la tua password prima di proseguire.</p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="d-flex flex-column gap-3">
            @csrf

            <div>
                <x-label for="password" value="Password corrente" class="fw-medium mb-1" />
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-lock"></i></span>
                    <x-input id="password" type="password" name="password" placeholder="••••••••" class="border-start-0" required autocomplete="current-password" autofocus />
                </div>
                <x-input-error for="password" class="mt-1" />
            </div>

            <x-button class="w-100 py-2 fs-6 fw-semibold shadow-sm mt-2">
                Conferma e continua <i class="bi bi-arrow-right ms-1"></i>
            </x-button>
        </form>
    </x-authentication-card>
</x-guest-layout>
