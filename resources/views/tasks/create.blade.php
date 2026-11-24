{{-- Form per la creazione di un nuovo task all'interno di un progetto --}}
<x-page title="Nuovo task" subtitle="Definisci una nuova attività per il progetto.">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-decoration-none">Progetti</a></li>
        <li class="breadcrumb-item"><a href="{{ route('projects.show', $project) }}" class="text-decoration-none">{{ $project->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('projects.tasks.index', $project) }}" class="text-decoration-none">Task</a></li>
        <li class="breadcrumb-item active text-dark fw-medium" aria-current="page">Nuovo task</li>
    </x-slot>

    <div class="row g-4">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('projects.tasks.store', $project) }}" class="card shadow-sm border-0 overflow-hidden">
                @csrf
                <div class="card-body p-4 d-flex flex-column gap-3">
                    <h2 class="h6 fw-bold text-dark mb-0">Parametri del task</h2>
                    @include('tasks._fields', ['task' => null, 'iterations' => $project->iterations])
                </div>
                <div class="card-footer bg-light py-3 px-4 border-top d-flex justify-content-end gap-2">
                    <a class="btn btn-light border" href="{{ route('projects.tasks.index', $project) }}">Annulla</a>
                    <x-button class="shadow-sm"><i class="bi bi-plus-lg me-1"></i>Crea task</x-button>
                </div>
            </form>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 small text-body-secondary">
                    <div class="d-flex align-items-center gap-2 mb-2 text-dark">
                        <i class="bi bi-info-circle-fill text-primary"></i>
                        <span class="fw-bold">Stato e assegnatari</span>
                    </div>
                    <p class="mb-0 lh-base">
                        Il task viene creato automaticamente in stato <strong class="text-dark">Da fare</strong>.
                        Dopo averlo creato potrai assegnarlo a uno o più colleghi e seguire il suo avanzamento nel workflow.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-page>
