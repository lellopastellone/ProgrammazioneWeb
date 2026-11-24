{{-- Form per la modifica del profilo, ruoli, team e stato di attivazione di un utente --}}
<x-page title="Modifica utente" subtitle="Aggiorna le informazioni anagrafiche e i ruoli dell'utente.">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none">Utenti</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.show', $user) }}" class="text-decoration-none">{{ $user->full_name }}</a></li>
        <li class="breadcrumb-item active text-dark fw-medium" aria-current="page">Modifica</li>
    </x-slot>

    <div class="row g-4">
        {{-- Colonna sinistra: Modulo di modifica dei dati anagrafici e dei team assegnati --}}
        <div class="col-lg-8">
            <form method="POST" action="{{ route('users.update', $user) }}" class="card shadow-sm border-0 overflow-hidden">
                @csrf
                @method('PUT')
                <div class="card-body p-4 d-flex flex-column gap-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <h2 class="h6 fw-bold text-dark mb-0">Dati anagrafici</h2>
                        @if (! $user->active)
                            <span class="badge bg-secondary-subtle text-secondary border">Account disattivato · Sola lettura</span>
                        @endif
                    </div>
                    @if (! $user->active)
                        <fieldset disabled>
                    @endif
                    @include('users._fields')
                    @if (! $user->active)
                        </fieldset>
                    @endif
                </div>
                <div class="card-footer bg-light py-3 px-4 border-top d-flex justify-content-end gap-2">
                    <a class="btn btn-light border" href="{{ route('users.show', $user) }}">Annulla</a>
                    @if ($user->active)
                        <x-button class="shadow-sm"><i class="bi bi-check-lg me-1"></i>Salva modifiche</x-button>
                    @endif
                </div>
            </form>
        </div>

        {{-- Colonna destra: Gestione dello stato dell'account (attivazione o disattivazione) --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h6 fw-bold text-dark mb-0">Stato account</h2>
                        @if ($user->active)
                            <span class="badge rounded-pill text-bg-success px-2 py-1 fw-medium">
                                <i class="bi bi-check-circle me-1"></i>Attivo
                            </span>
                        @else
                            <span class="badge rounded-pill bg-light text-secondary border px-2 py-1 fw-medium">
                                <i class="bi bi-dash-circle me-1"></i>Disattivato
                            </span>
                        @endif
                    </div>
                    <p class="small text-body-secondary mb-0">
                        {{ $user->active ? 'Disattivando questo utente gli verrà impedito l\'accesso e non potrà essere assegnato a nuovi task.' : 'Riattivando questo account l\'utente potrà nuovamente accedere e collaborare.' }}
                    </p>
                    <form method="POST" action="{{ route('users.active.update', $user) }}" @if ($user->active) data-confirm="Disattivare l'account di {{ $user->full_name }}? Non potrà più accedere alla piattaforma." data-confirm-button="Disattiva" @endif>
                        @csrf
                        @method('PATCH')
                        <button class="btn {{ $user->active ? 'btn-outline-danger' : 'btn-outline-success' }} w-100 shadow-sm mt-1">
                            <i class="bi {{ $user->active ? 'bi-person-slash' : 'bi-person-check' }} me-1"></i>{{ $user->active ? 'Disattiva account' : 'Riattiva account' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
