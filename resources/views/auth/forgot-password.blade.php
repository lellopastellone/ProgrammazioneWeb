{{-- Schermata di richiesta recupero password tramite invio del link via email --}}
<x-guest-layout>
    <x-authentication-card>
        <div class="mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 48px; height: 48px;">
                <i class="bi bi-key fs-4"></i>
            </div>
            <h1 class="h3 fw-bold text-dark mb-2">Password dimenticata?</h1>
            <p class="text-body-secondary small mb-0">Nessun problema. Inserisci la tua email e ti invieremo un link sicuro per reimpostarla.</p>
        </div>

        @session('status')
            <div class="alert alert-success d-flex align-items-center gap-2 small mb-3">
                <i class="bi bi-check-circle-fill"></i>
                <div>{{ $value }}</div>
            </div>
        @endsession

        <form method="POST" action="{{ route('password.email') }}" class="d-flex flex-column gap-3">
            @csrf

            <div>
                <x-label for="email" value="Email" class="fw-medium mb-1" />
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-envelope"></i></span>
                    <x-input id="email" type="email" name="email" :value="old('email')" placeholder="nome@azienda.it" class="border-start-0" required autofocus autocomplete="username" />
                </div>
                <x-input-error for="email" class="mt-1" />
            </div>

            <x-button class="w-100 py-2 fs-6 fw-semibold shadow-sm mt-2">
                Invia link di ripristino <i class="bi bi-send ms-1"></i>
            </x-button>
            
            <a class="small text-center text-decoration-none text-secondary hover-primary mt-2" href="{{ route('login') }}">
                <i class="bi bi-arrow-left me-1"></i> Torna alla pagina di accesso
            </a>
        </form>
    </x-authentication-card>
</x-guest-layout>

