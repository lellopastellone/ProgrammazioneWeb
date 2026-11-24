{{-- Modulo per la modifica della password dell'utente autenticato --}}
<x-form-section submit="updatePassword">
    <x-slot name="title">Aggiorna password</x-slot>
    <x-slot name="description">Assicurati che il tuo account utilizzi una password lunga e complessa per rimanere protetto.</x-slot>

    <x-slot name="form">
        {{-- Notifica di successo per password aggiornata (Alpine + Livewire) --}}
        <div class="col-12"
             x-data="{ shown: false }"
             x-init="@this.on('saved', () => { shown = true })"
             x-show="shown"
             x-transition.opacity.duration.300ms
             style="display: none;">
            <div class="alert alert-success d-flex align-items-center justify-content-between mb-0" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Password aggiornata con successo.</span>
                </div>
                <button type="button" class="btn-close" aria-label="Chiudi" @click="shown = false"></button>
            </div>
        </div>

        {{-- Verifica dell'identità: inserimento della password attuale --}}
        <div class="col-12">
            <x-label for="current_password" value="Password attuale" class="fw-medium small mb-1" />
            <div class="input-group">
                <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-shield-lock"></i></span>
                <x-input id="current_password" type="password" class="border-start-0" wire:model="state.current_password" autocomplete="current-password" />
            </div>
            <x-input-error for="current_password" class="mt-1" />
        </div>

        {{-- Nuova password e relativa conferma --}}
        <div class="col-12">
            <x-label for="password" value="Nuova password" class="fw-medium small mb-1" />
            <div class="input-group">
                <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-key"></i></span>
                <x-input id="password" type="password" class="border-start-0" wire:model="state.password" autocomplete="new-password" />
            </div>
            <x-input-error for="password" class="mt-1" />
        </div>

        <div class="col-12">
            <x-label for="password_confirmation" value="Conferma nuova password" class="fw-medium small mb-1" />
            <div class="input-group">
                <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-check-circle"></i></span>
                <x-input id="password_confirmation" type="password" class="border-start-0" wire:model="state.password_confirmation" autocomplete="new-password" />
            </div>
            <x-input-error for="password_confirmation" class="mt-1" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-button class="btn-sm px-3" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="updatePassword">Aggiorna password</span>
            <span wire:loading wire:target="updatePassword"><span class="spinner-border spinner-border-sm me-1"></span> Salvataggio...</span>
        </x-button>
    </x-slot>
</x-form-section>
