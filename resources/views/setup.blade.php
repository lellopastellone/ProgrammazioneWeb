{{-- Schermata di configurazione iniziale (primo avvio) per la creazione dell'organizer e SMTP --}}
<x-guest-layout>
    <x-authentication-card>
        <x-slot name="footer">
            <span class="d-inline-flex align-items-center gap-1 text-secondary">
                <i class="bi bi-info-circle"></i> Disponibile solo durante l'installazione iniziale finché non esiste un account organizer.
            </span>
        </x-slot>

        {{-- Intestazione della procedura guidata di configurazione iniziale --}}
        <div class="mb-4">
            <span class="badge rounded-pill text-bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1 fw-medium mb-3">
                <i class="bi bi-rocket-takeoff me-1"></i> Configurazione iniziale
            </span>
            <h1 class="h3 fw-bold text-dark mb-1">Inizializza FlowDesk</h1>
            <p class="text-body-secondary small mb-0">Crea l'account proprietario dell'organizzazione e configura i parametri aziendali.</p>
        </div>

        {{-- Il campo domini si riempie da solo con il dominio dell'email, finché non lo si modifica a mano. --}}
        <form method="POST" action="{{ route('setup.store') }}" class="d-flex flex-column gap-3"
              x-data="{ domains: @js(old('domains', '')), touched: @js(filled(old('domains'))) }">
            @csrf

            {{-- Sezione 1: Credenziali di accesso e domini ammessi per l'Organizer --}}
            <div class="p-3 bg-light rounded-3 border">
                <div class="fw-semibold text-dark small mb-3 d-flex align-items-center gap-2">
                    <span class="badge rounded-circle text-bg-primary" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">1</span>
                    Profilo Organizer
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <x-label for="name" value="Nome" class="fw-medium mb-1 small" />
                        <x-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="given-name" placeholder="Mario" />
                        <x-input-error for="name" class="mt-1" />
                    </div>
                    <div class="col-sm-6">
                        <x-label for="surname" value="Cognome" class="fw-medium mb-1 small" />
                        <x-input id="surname" type="text" name="surname" :value="old('surname')" required autocomplete="family-name" placeholder="Rossi" />
                        <x-input-error for="surname" class="mt-1" />
                    </div>
                </div>

                <div class="mb-3">
                    <x-label for="email" value="Email aziendale" class="fw-medium mb-1 small" />
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-secondary"><i class="bi bi-envelope"></i></span>
                        <x-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="mario.rossi@azienda.it" class="border-start-0"
                                 x-on:input="touched || (domains = $event.target.value.includes('@') ? $event.target.value.split('@').pop() : '')" />
                    </div>
                    <x-input-error for="email" class="mt-1" />
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <x-label for="domains" value="Domini email ammessi" class="fw-medium mb-0 small" />
                        <span class="text-body-secondary" style="font-size: 0.75rem;">Separati da virgola</span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-secondary"><i class="bi bi-globe"></i></span>
                        <x-input id="domains" type="text" name="domains" :value="old('domains')" required placeholder="azienda.it, azienda.com"
                                 x-model="domains" x-on:input="touched = true" class="border-start-0" />
                    </div>
                    <x-input-error for="domains" class="mt-1" />
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <x-label for="password" value="Password" class="fw-medium mb-1 small" />
                        <x-input id="password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
                        <x-input-error for="password" class="mt-1" />
                    </div>
                    <div class="col-sm-6">
                        <x-label for="password_confirmation" value="Conferma password" class="fw-medium mb-1 small" />
                        <x-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                    </div>
                </div>
            </div>

            {{-- Sezione 2: Parametri di configurazione del server SMTP per l'invio delle email di sistema --}}
            <div class="p-3 bg-light rounded-3 border">
                <div class="fw-semibold text-dark small mb-3 d-flex align-items-center gap-2">
                    <span class="badge rounded-circle text-bg-primary" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">2</span>
                    Server di posta (SMTP)
                </div>
                @include('partials.smtp-fields', ['smtp' => \App\Models\MailSetting::current()])
            </div>

            <x-button class="w-100 py-2 fs-6 fw-semibold shadow-sm mt-2">
                Completa installazione <i class="bi bi-arrow-right ms-1"></i>
            </x-button>
        </form>
    </x-authentication-card>
</x-guest-layout>
