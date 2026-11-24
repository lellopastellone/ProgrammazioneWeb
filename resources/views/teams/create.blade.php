{{-- Form per la creazione di un nuovo team o reparto aziendale --}}
<x-page title="Nuovo team" subtitle="Crea un nuovo gruppo di lavoro all'interno dell'organizzazione.">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('teams.index') }}" class="text-decoration-none">Team</a></li>
        <li class="breadcrumb-item active text-dark fw-medium" aria-current="page">Nuovo team</li>
    </x-slot>

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('teams.store') }}" class="card shadow-sm border-0 overflow-hidden">
                @csrf
                <div class="card-body p-4 d-flex flex-column gap-3">
                    <h2 class="h6 fw-bold text-dark mb-0">Parametri del team</h2>
                    @include('teams._fields', ['team' => null])
                </div>
                <div class="card-footer bg-light py-3 px-4 border-top d-flex justify-content-end gap-2">
                    <a class="btn btn-light border" href="{{ route('teams.index') }}">Annulla</a>
                    <x-button class="shadow-sm"><i class="bi bi-plus-lg me-1"></i>Crea team</x-button>
                </div>
            </form>
        </div>
    </div>
</x-page>

