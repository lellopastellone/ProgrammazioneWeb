{{-- Schermata per l'inserimento del codice di autenticazione a due fattori (2FA) o codice di recupero.
     Con $verify = true (TwoFactorVerifyController) è la conferma prima di modifica utenti e server SMTP, non il login. --}}
<x-guest-layout>
    <x-authentication-card>
        <div x-data="{ recovery: false }">
            <div class="mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 48px; height: 48px;">
                    <i class="bi bi-shield-check fs-4"></i>
                </div>
                <h1 class="h3 fw-bold text-dark mb-2">Verifica a due fattori</h1>
                <p class="text-body-secondary small mb-0" x-show="! recovery">Inserisci il codice di autenticazione a 6 cifre generato dalla tua app.</p>
                <p class="text-body-secondary small mb-0" x-show="recovery" style="display: none;">Inserisci uno dei codici di recupero salvati in fase di configurazione.</p>
            </div>

            <x-validation-errors />

            <form method="POST" action="{{ isset($verify) ? route('two-factor.verify.store') : route('two-factor.login') }}" class="d-flex flex-column gap-3">
                @csrf

                <div x-show="! recovery">
                    <x-label for="code" value="Codice a 6 cifre" class="fw-medium mb-1 text-center d-block" />
                    <x-input id="code" class="text-center font-monospace fs-2 py-2 letter-spacing-2" style="letter-spacing: 0.3em;" type="text" inputmode="numeric" name="code" placeholder="000 000" autofocus x-ref="code" autocomplete="one-time-code" maxlength="7" />
                </div>

                <div x-show="recovery" style="display: none;">
                    <x-label for="recovery_code" value="Codice di recupero" class="fw-medium mb-1" />
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-key"></i></span>
                        <x-input id="recovery_code" class="font-monospace border-start-0" type="text" name="recovery_code" placeholder="xxxx-xxxx-xxxx" x-ref="recovery_code" autocomplete="one-time-code" />
                    </div>
                </div>

                <x-button class="w-100 py-2 fs-6 fw-semibold shadow-sm mt-2">
                    {{ isset($verify) ? 'Verifica e continua' : 'Verifica e accedi' }} <i class="bi bi-check-lg ms-1"></i>
                </x-button>

                <div class="text-center pt-2">
                    <button type="button" class="btn btn-link btn-sm text-decoration-none fw-medium text-secondary hover-primary" x-show="! recovery"
                            x-on:click="recovery = true; $nextTick(() => { $refs.recovery_code.focus() })">
                        <i class="bi bi-question-circle me-1"></i> Usa un codice di emergenza
                    </button>
                    <button type="button" class="btn btn-link btn-sm text-decoration-none fw-medium text-secondary hover-primary" x-show="recovery" style="display: none;"
                            x-on:click="recovery = false; $nextTick(() => { $refs.code.focus() })">
                        <i class="bi bi-phone me-1"></i> Usa l'app authenticator
                    </button>
                </div>
            </form>
        </div>
    </x-authentication-card>
</x-guest-layout>
