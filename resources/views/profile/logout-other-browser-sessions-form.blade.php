{{-- Modulo per visualizzare e terminare le altre sessioni di navigazione attive sui diversi dispositivi --}}
<x-action-section>
    <x-slot name="title">Sessioni del browser</x-slot>
    <x-slot name="description">Gestisci e chiudi le sessioni attive su altri browser o dispositivi.</x-slot>

    <x-slot name="content">
        <p class="small text-secondary mb-3">Se pensi che il tuo account sia compromesso o hai dimenticato la sessione aperta su un computer condiviso, puoi forzare la disconnessione da tutte le altre sessioni.</p>

        {{-- Elenco dei dispositivi e browser con sessioni attualmente attive --}}
        @if (count($this->sessions) > 0)
            <div class="list-group list-group-flush border rounded-3 mb-3 overflow-hidden">
                @foreach ($this->sessions as $session)
                    <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-light text-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi {{ $session->agent->isDesktop() ? 'bi-laptop' : 'bi-phone' }} fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-medium text-dark small">{{ $session->agent->platform() ?: 'Sconosciuto' }} · {{ $session->agent->browser() ?: 'Sconosciuto' }}</div>
                                <div class="text-body-secondary small">
                                    <span class="font-monospace">{{ $session->ip_address }}</span>
                                    @if (! $session->is_current_device)
                                        · ultimo accesso {{ $session->last_active }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if ($session->is_current_device)
                            <span class="badge text-bg-success-subtle text-success border border-success-subtle fw-medium">Questo dispositivo</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Notifica di successo dopo la disconnessione (gestita con Alpine e Livewire) --}}
        <div class="mb-3"
             x-data="{ shown: false }"
             x-init="@this.on('loggedOut', () => { shown = true })"
             x-show="shown"
             x-transition.opacity.duration.300ms
             style="display: none;">
            <div class="alert alert-success d-flex align-items-center justify-content-between mb-0" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Tutte le altre sessioni del browser sono state disconnesse con successo.</span>
                </div>
                <button type="button" class="btn-close" aria-label="Chiudi" @click="shown = false"></button>
            </div>
        </div>

        {{-- Pulsante principale: apre la finestra modale di conferma per motivi di sicurezza --}}
        <div class="d-flex align-items-center gap-3">
            <x-secondary-button class="btn-sm" wire:click="confirmLogout" wire:loading.attr="disabled">
                <i class="bi bi-box-arrow-right me-1"></i> Disconnetti altre sessioni
            </x-secondary-button>
        </div>

        {{-- Finestra modale con richiesta della password attuale per confermare l'operazione --}}
        <x-dialog-modal wire:model.live="confirmingLogout">
            {{-- Titolo mostrato in cima al popup modale aperto --}}
            <x-slot name="title">Disconnetti altre sessioni</x-slot>

            <x-slot name="content">
                <p>Inserisci la tua password attuale per confermare la disconnessione da tutti gli altri dispositivi e browser.</p>

                <div class="mt-3" x-data="{}" x-on:confirming-logout-other-browser-sessions.window="setTimeout(() => $refs.password.focus(), 250)">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-secondary"><i class="bi bi-shield-lock"></i></span>
                        <x-input type="password" autocomplete="current-password" placeholder="Password attuale"
                                    class="border-start-0"
                                    x-ref="password"
                                    wire:model="password"
                                    wire:keydown.enter="logoutOtherBrowserSessions" />
                    </div>
                    <x-input-error for="password" class="mt-1" />
                </div>
            </x-slot>

            {{-- Footer del popup: annulla oppure esegue la disconnessione effettiva tramite il pulsante rosso --}}
            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('confirmingLogout')" wire:loading.attr="disabled">Annulla</x-secondary-button>
                <x-danger-button class="ms-2" wire:click="logoutOtherBrowserSessions" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="logoutOtherBrowserSessions">Disconnetti</span>
                    <span wire:loading wire:target="logoutOtherBrowserSessions"><span class="spinner-border spinner-border-sm me-1"></span> Elaborazione...</span>
                </x-danger-button>
            </x-slot>
        </x-dialog-modal>
    </x-slot>
</x-action-section>
