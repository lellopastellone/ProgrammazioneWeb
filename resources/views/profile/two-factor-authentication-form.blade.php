{{-- Modulo per l'attivazione, configurazione e recupero codici dell'autenticazione a due fattori (2FA) --}}
<x-action-section>
    <x-slot name="title">Autenticazione a due fattori</x-slot>
    <x-slot name="description">Aggiungi un codice da app di autenticazione al login per proteggere il tuo account.</x-slot>

    <x-slot name="content">
        {{-- Banner informativo sullo stato attuale dell'autenticazione a due fattori --}}
        @if (! $this->enabled)
            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-3 border">
                <div class="rounded-circle bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="bi bi-shield-slash fs-4"></i>
                </div>
                <div>
                    <h3 class="h6 fw-semibold text-dark mb-0">Non hai attivato l'autenticazione a due fattori</h3>
                    <p class="small text-secondary mb-0">Quando è attiva, ad ogni accesso ti verrà richiesto un codice numerico temporaneo generato dalla tua applicazione di autenticazione.</p>
                </div>
            </div>
        @else
            <div class="d-flex align-items-center gap-3 p-3 bg-success-subtle text-success-emphasis rounded-3 mb-3 border border-success-subtle">
                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="bi bi-shield-check fs-4"></i>
                </div>
                <div>
                    <h3 class="h6 fw-semibold text-success-emphasis mb-0">
                        {{ $showingConfirmation ? 'Completa l\'attivazione della 2FA' : 'Autenticazione a due fattori attiva' }}
                    </h3>
                    <p class="small mb-0">
                        {{ $showingConfirmation ? 'Inquadra il QR code con l\'app di autenticazione e inserisci il codice per verificare la corretta configurazione.' : 'Il tuo account è protetto con autenticazione a due fattori.' }}
                    </p>
                </div>
            </div>
        @endif

        @if ($this->enabled)
            {{-- Configurazione del codice QR e verifica del codice a 6 cifre dall'app --}}
            @if ($showingQrCode)
                <div class="card bg-light border-0 p-3 mb-3 text-center">
                    <p class="small fw-semibold text-dark mb-2">
                        {{ $showingConfirmation
                            ? 'Scansiona il QR code con l\'app di autenticazione (o inserisci la chiave segreta):'
                            : 'Autenticazione attiva. Scansiona il codice QR con la tua app:' }}
                    </p>

                    <div class="d-inline-block bg-white p-3 rounded-3 shadow-sm mx-auto mb-2">
                        {!! $this->user->twoFactorQrCodeSvg() !!}
                    </div>

                    <p class="small text-secondary mb-0">
                        Chiave segreta: <code class="fw-semibold text-dark bg-white px-2 py-1 rounded border">{{ decrypt($this->user->two_factor_secret) }}</code>
                    </p>

                    @if ($showingConfirmation)
                        <div class="col-sm-6 mx-auto mt-3">
                            <x-label for="code" value="Codice a 6 cifre dall'app" class="fw-medium small mb-1" />
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-secondary"><i class="bi bi-phone"></i></span>
                                <x-input id="code" type="text" name="code" class="font-monospace fs-5 text-center border-start-0" inputmode="numeric" autofocus autocomplete="one-time-code"
                                    wire:model="code"
                                    wire:keydown.enter="confirmTwoFactorAuthentication"
                                    placeholder="000 000" />
                            </div>
                            <x-input-error for="code" class="mt-1" />
                        </div>
                    @endif
                </div>
            @endif

            {{-- Codici di recupero d'emergenza in caso di smarrimento del dispositivo --}}
            @if ($showingRecoveryCodes)
                <div class="alert alert-warning border-warning-subtle mb-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5 text-warning"></i>
                        <span class="fw-semibold">Codici di recupero d'emergenza</span>
                    </div>
                    <p class="small text-secondary mb-2">Conserva questi codici in un luogo sicuro. Servono per accedere se perdi il dispositivo di autenticazione.</p>
                    <div class="bg-white border rounded p-3 font-monospace small row g-2">
                        @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                            <div class="col-6 col-md-4 text-dark fw-medium">{{ $code }}</div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        {{-- Azioni per abilitare, verificare, mostrare/rigenerare codici o disattivare la 2FA --}}
        <div class="d-flex flex-wrap gap-2 mt-3">
            @if (! $this->enabled)
                <x-confirms-password wire:then="enableTwoFactorAuthentication">
                    <x-button type="button" class="btn-sm btn-primary" wire:loading.attr="disabled">
                        <i class="bi bi-shield-plus me-1"></i> Attiva autenticazione 2FA
                    </x-button>
                </x-confirms-password>
            @else
                @if ($showingRecoveryCodes)
                    <x-confirms-password wire:then="regenerateRecoveryCodes">
                        <x-secondary-button class="btn-sm">
                            <i class="bi bi-arrow-repeat me-1"></i> Rigenera codici di recupero
                        </x-secondary-button>
                    </x-confirms-password>
                @elseif ($showingConfirmation)
                    <x-confirms-password wire:then="confirmTwoFactorAuthentication">
                        <x-button type="button" class="btn-sm btn-primary" wire:loading.attr="disabled">
                            <i class="bi bi-check2 me-1"></i> Conferma codice
                        </x-button>
                    </x-confirms-password>
                @else
                    <x-confirms-password wire:then="showRecoveryCodes">
                        <x-secondary-button class="btn-sm">
                            <i class="bi bi-key me-1"></i> Mostra codici di recupero
                        </x-secondary-button>
                    </x-confirms-password>
                @endif

                <x-confirms-password wire:then="disableTwoFactorAuthentication">
                    @if ($showingConfirmation)
                        <x-secondary-button class="btn-sm" wire:loading.attr="disabled">Annulla</x-secondary-button>
                    @else
                        <x-danger-button class="btn-sm" wire:loading.attr="disabled">
                            <i class="bi bi-shield-x me-1"></i> Disattiva 2FA
                        </x-danger-button>
                    @endif
                </x-confirms-password>
            @endif
        </div>
    </x-slot>
</x-action-section>
