{{-- Modulo per l'aggiornamento dei dati anagrafici (nome, email) e della foto profilo --}}
<x-form-section submit="updateProfileInformation">
    <x-slot name="title">Informazioni profilo</x-slot>
    <x-slot name="description">Aggiorna le informazioni di base e l'indirizzo email del tuo account.</x-slot>

    <x-slot name="form">
        {{-- Notifica di avvenuto salvataggio delle modifiche (gestita con Alpine e Livewire) --}}
        <div class="col-12"
             x-data="{
                shown: @js(session()->has('profile_updated')),
                message: @js(session('profile_updated', 'Informazioni del profilo modificate correttamente.'))
             }"
             x-init="
                $wire.on('saved', () => {
                    message = 'Informazioni del profilo modificate correttamente.';
                    shown = true;
                });
             "
             x-show="shown"
             x-transition.opacity.duration.300ms
             style="{{ session()->has('profile_updated') ? '' : 'display: none;' }}">
            <div class="alert alert-success d-flex align-items-center justify-content-between mb-0" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <span x-text="message">{{ session('profile_updated', 'Informazioni del profilo modificate correttamente.') }}</span>
                </div>
                <button type="button" class="btn-close" aria-label="Chiudi" @click="shown = false"></button>
            </div>
        </div>

        {{-- Gestione e caricamento della foto profilo --}}
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div class="col-12"
                 x-data="{ photoName: null, photoPreview: null }"
                 x-init="
                    $wire.on('saved', () => { photoPreview = null; if ($refs.photo) $refs.photo.value = ''; });
                    $wire.on('refresh-navigation-menu', () => { photoPreview = null; if ($refs.photo) $refs.photo.value = ''; });
                 ">
                <x-label for="photo" value="Foto profilo" class="fw-medium small mb-2" />

                {{-- Input file nascosto: gestito da Livewire e attivato al click sulla label sottostante --}}
                <input type="file"
                       id="photo"
                       class="d-none"
                       wire:model.live="photo"
                       x-ref="photo"
                       accept="image/png, image/jpeg, image/jpg, image/webp"
                       x-on:change="
                            if ($refs.photo.files && $refs.photo.files[0]) {
                                photoName = $refs.photo.files[0].name;
                                const reader = new FileReader();
                                reader.onload = (e) => {
                                    photoPreview = e.target.result;
                                };
                                reader.readAsDataURL($refs.photo.files[0]);
                            }
                       " />

                <div class="d-flex align-items-center gap-3">
                    {{-- Anteprima istantanea lato client tramite FileReader di Alpine prima dell'upload al server --}}
                    <div x-show="photoPreview" style="display: none;">
                        <img x-bind:src="photoPreview"
                             alt="Anteprima nuova foto"
                             class="rounded-circle object-fit-cover border shadow-sm"
                             style="width: 64px; height: 64px;">
                    </div>

                    {{-- Foto corrente memorizzata sul server o avatar predefinito se non c'è una nuova selezione --}}
                    <div x-show="!photoPreview">
                        @if ($photo && ! $errors->has('photo'))
                            @php
                                try {
                                    $tempUrl = $photo->temporaryUrl();
                                } catch (\Throwable $e) {
                                    $tempUrl = null;
                                }
                            @endphp
                            @if ($tempUrl)
                                <img src="{{ $tempUrl }}"
                                     alt="Anteprima foto"
                                     class="rounded-circle object-fit-cover border shadow-sm"
                                     style="width: 64px; height: 64px;">
                            @else
                                <div class="d-flex align-items-center">
                                    <x-avatar :user="$this->user" size="lg" />
                                </div>
                            @endif
                        @else
                            <div class="d-flex align-items-center">
                                <x-avatar :user="$this->user" size="lg" />
                            </div>
                        @endif
                    </div>

                    {{-- Controlli operativi: selezione di una nuova foto o rimozione di quella esistente --}}
                    <div class="d-flex flex-column gap-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <label for="photo"
                                   class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 mb-0"
                                   style="cursor: pointer;">
                                <i class="bi bi-camera"></i>
                                <span>Seleziona una foto</span>
                            </label>

                            @if ($this->user->profile_photo_path)
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1"
                                        x-on:click="window.confirmAction('Sei sicuro di voler rimuovere la tua foto profilo? L\'operazione non può essere annullata.', () => $wire.deleteProfilePhoto(), 'Rimuovi')"
                                        wire:loading.attr="disabled">
                                    <i class="bi bi-trash"></i>
                                    <span wire:loading.remove wire:target="deleteProfilePhoto">Rimuovi foto</span>
                                    <span wire:loading wire:target="deleteProfilePhoto"><span class="spinner-border spinner-border-sm me-1"></span> Rimozione...</span>
                                </button>
                            @endif
                        </div>

                        <div wire:loading wire:target="photo" class="text-primary small">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                            <span>Elaborazione immagine...</span>
                        </div>

                        <span class="text-body-secondary small" style="font-size: 0.8rem;">
                            Formati supportati: JPG, PNG, WEBP. Dimensione massima: 5 MB.
                        </span>
                    </div>
                </div>

                <x-input-error for="photo" class="mt-2" />
            </div>
        @endif

        {{-- Campi anagrafici di base (Nome e Cognome) --}}
        <div class="col-sm-6">
            <x-label for="name" value="Nome" class="fw-medium small mb-1" />
            <div class="input-group">
                <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-person"></i></span>
                <x-input id="name" type="text" class="border-start-0" wire:model="state.name" required autocomplete="given-name" />
            </div>
            <x-input-error for="name" class="mt-1" />
        </div>

        <div class="col-sm-6">
            <x-label for="surname" value="Cognome" class="fw-medium small mb-1" />
            <div class="input-group">
                <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-person"></i></span>
                <x-input id="surname" type="text" class="border-start-0" wire:model="state.surname" required autocomplete="family-name" />
            </div>
            <x-input-error for="surname" class="mt-1" />
        </div>

        {{-- Indirizzo email e password attuale (richiesta per sicurezza solo se si modifica l'email) --}}
        <div class="col-12">
            <x-label for="email" value="Indirizzo Email" class="fw-medium small mb-1" />
            <div class="input-group">
                <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-envelope"></i></span>
                <x-input id="email" type="email" class="border-start-0" wire:model="state.email" required autocomplete="username" />
            </div>
            <x-input-error for="email" class="mt-1" />
        </div>

        {{-- Compare solo quando l'email cambia: UpdateUserProfileInformation chiede la password attuale. --}}
        <div class="col-12" x-show="$wire.state.email !== @js($this->user->email)" style="display: none;">
            <x-label for="profile_current_password" value="Password attuale" class="fw-medium small mb-1" />
            <div class="input-group">
                <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-shield-lock"></i></span>
                <x-input id="profile_current_password" type="password" class="border-start-0" wire:model="state.current_password" autocomplete="current-password" />
            </div>
            <x-input-error for="current_password" class="mt-1" />
        </div>

        {{-- Ruolo aziendale associato all'account utente corrente --}}
        <div class="col-12">
            <div class="p-3 bg-light rounded-3 d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-primary fs-5"></i>
                <div class="small">
                    <span class="text-body-secondary">Ruolo account:</span>
                    <strong class="text-body ms-1">{{ $this->user->getRoleNames()->map(fn ($r) => ucfirst(str_replace('_', ' ', $r)))->join(', ') }}</strong>
                </div>
            </div>
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-button class="btn-sm px-3" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="updateProfileInformation">Salva modifiche</span>
            <span wire:loading wire:target="updateProfileInformation"><span class="spinner-border spinner-border-sm me-1"></span> Salvataggio...</span>
        </x-button>
    </x-slot>
</x-form-section>
