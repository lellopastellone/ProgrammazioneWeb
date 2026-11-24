{{-- Form per l'invito e la registrazione di un nuovo utente nel sistema --}}
<x-page title="Nuovo utente" subtitle="Registra un nuovo collaboratore nell'organizzazione.">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none">Utenti</a></li>
        <li class="breadcrumb-item active text-dark fw-medium" aria-current="page">Nuovo utente</li>
    </x-slot>

    <div class="row g-4">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('users.store') }}" class="card shadow-sm border-0 overflow-hidden">
                @csrf
                <div class="card-body p-4 d-flex flex-column gap-4">
                    <h2 class="h6 fw-bold text-dark mb-0">Informazioni account</h2>
                    @include('users._fields', ['user' => null])
                </div>
                <div class="card-footer bg-light py-3 px-4 border-top d-flex justify-content-end gap-2">
                    <a class="btn btn-light border" href="{{ route('users.index') }}">Annulla</a>
                    <x-button class="shadow-sm"><i class="bi bi-person-plus me-1"></i>Crea utente</x-button>
                </div>
            </form>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 small text-body-secondary">
                    <div class="d-flex align-items-center gap-2 mb-2 text-dark">
                        <i class="bi bi-shield-lock-fill text-primary"></i>
                        <span class="fw-bold">Generazione credenziali</span>
                    </div>
                    <p class="mb-0 lh-base">
                        Viene generata automaticamente una password provvisoria sicura. Il nuovo utente riceverà una mail di benvenuto contenente un link personale per impostare la propria password.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-page>
