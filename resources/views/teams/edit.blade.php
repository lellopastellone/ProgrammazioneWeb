{{-- Form per la modifica del nome e della descrizione di un team esistente --}}
<x-page title="Modifica team" subtitle="Aggiorna le informazioni e gestisci i membri del team.">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('teams.index') }}" class="text-decoration-none">Team</a></li>
        <li class="breadcrumb-item"><a href="{{ route('teams.show', $team) }}" class="text-decoration-none">{{ $team->name }}</a></li>
        <li class="breadcrumb-item active text-dark fw-medium" aria-current="page">Modifica</li>
    </x-slot>

    <div class="row g-4">
        <div class="col-lg-8 d-flex flex-column gap-4">
            <form method="POST" action="{{ route('teams.update', $team) }}" class="card shadow-sm border-0 overflow-hidden">
                @csrf
                @method('PUT')
                <div class="card-body p-4 d-flex flex-column gap-3">
                    <h2 class="h6 fw-bold text-dark mb-0">Informazioni generali</h2>
                    @include('teams._fields')
                </div>
                <div class="card-footer bg-light py-3 px-4 border-top d-flex justify-content-end gap-2">
                    <a class="btn btn-light border" href="{{ route('teams.show', $team) }}">Annulla</a>
                    <x-button class="shadow-sm"><i class="bi bi-check-lg me-1"></i>Salva modifiche</x-button>
                </div>
            </form>
            @include('teams._members', ['manage' => true])
        </div>
        <div class="col-lg-4 d-flex flex-column gap-4">
            {{-- Card con ricerca (GET ?q=) e aggiunta rapida (POST) centralizzata tramite componente --}}
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 d-flex flex-column gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-plus text-primary fs-5"></i>
                        <h2 class="h6 fw-bold mb-0 text-dark">Aggiungi membro</h2>
                    </div>
                    <p class="small text-body-secondary mb-0">Cerca e seleziona un collaboratore da associare a questo team.</p>
                    <x-candidate-picker 
                        :action="route('teams.members.store', $team)"
                        :candidates="$candidates"
                        button-label="Aggiungi"
                        empty-text="Tutti i collaboratori attivi fanno già parte di questo team."
                    />
                </div>
            </div>
            @include('teams._delete')
        </div>
    </div>
</x-page>
