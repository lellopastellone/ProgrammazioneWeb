{{-- Form per la modifica delle informazioni di un task esistente --}}
<x-page title="Modifica task" subtitle="Aggiorna le informazioni e la pianificazione dell'attività.">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-decoration-none">Progetti</a></li>
        <li class="breadcrumb-item"><a href="{{ route('projects.show', $task->project) }}" class="text-decoration-none">{{ $task->project->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">{{ $task->title }}</a></li>
        <li class="breadcrumb-item active text-dark fw-medium" aria-current="page">Modifica</li>
    </x-slot>

    <div class="row g-4">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('tasks.update', $task) }}" class="card shadow-sm border-0 overflow-hidden">
                @csrf
                @method('PUT')
                <div class="card-body p-4 d-flex flex-column gap-3">
                    <h2 class="h6 fw-bold text-dark mb-0">Parametri del task</h2>
                    @include('tasks._fields', ['iterations' => $task->project->iterations])
                </div>
                <div class="card-footer bg-light py-3 px-4 border-top d-flex justify-content-end gap-2">
                    <a class="btn btn-light border" href="{{ route('tasks.show', $task) }}">Annulla</a>
                    <x-button class="shadow-sm"><i class="bi bi-check-lg me-1"></i>Salva modifiche</x-button>
                </div>
            </form>
        </div>
        <div class="col-lg-4 d-flex flex-column gap-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 small text-body-secondary">
                    <div class="d-flex align-items-center gap-2 mb-2 text-dark">
                        <i class="bi bi-info-circle-fill text-primary"></i>
                        <span class="fw-bold">Stato e assegnatari</span>
                    </div>
                    <p class="mb-0 lh-base">
                        Lo stato non si modifica da qui: avanza o retrocede tramite le azioni di workflow nella pagina principale del task. Anche gli assegnatari si gestiscono direttamente da lì.
                    </p>
                </div>
            </div>
            
            <div class="card shadow-sm border-0 border-top border-danger border-3">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold text-danger mb-2">Elimina task</h2>
                    <p class="small text-body-secondary mb-3">Elimina definitivamente questo task, tutti i commenti associati e le assegnazioni.</p>
                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" data-confirm="Eliminare definitivamente il task «{{ $task->title }}»? Questa azione non può essere annullata." data-confirm-button="Elimina">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Elimina task</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
