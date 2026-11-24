{{-- Testata comune alle pagine di un progetto. Parametri: $project, $tab (show|tasks|calendar|members|edit), $crumb (opzionale). --}}
<nav aria-label="breadcrumb">
    <ol class="breadcrumb small mb-2">
        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-decoration-none">Progetti</a></li>
        @isset($crumb)
            <li class="breadcrumb-item"><a href="{{ route('projects.show', $project) }}" class="text-decoration-none">{{ $project->name }}</a></li>
            <li class="breadcrumb-item active fw-medium text-dark" aria-current="page">{{ $crumb }}</li>
        @else
            <li class="breadcrumb-item active fw-medium text-dark" aria-current="page">{{ $project->name }}</li>
        @endisset
    </ol>
</nav>

{{-- Titolo del progetto, badge di stato e pulsanti operativi autorizzati (@can) --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
    <div class="d-flex flex-wrap align-items-center gap-3">
        <h1 class="h3 fw-bold mb-0 text-dark">{{ $project->name }}</h1>
        <x-status :value="$project->status" />
    </div>
    @can('manage', $project)
        @if (($tab ?? null) !== 'edit' || $project->isEditable())
            <div class="d-flex gap-2">
                @if (($tab ?? null) !== 'edit')
                    <a class="btn btn-light border shadow-sm" href="{{ route('projects.edit', $project) }}">
                        <i class="bi bi-gear me-1"></i>Impostazioni
                    </a>
                @endif
                @can('update', $project)
                    <a class="btn btn-primary shadow-sm" href="{{ route('projects.tasks.create', $project) }}"><i class="bi bi-plus-lg me-1"></i>Nuovo task</a>
                @endcan
            </div>
        @endif
    @endcan
</div>

{{-- Schede di navigazione interna del progetto (Panoramica, Task, Calendario, Membri) --}}
<ul class="nav nav-underline border-bottom mb-4">
    @foreach ([
        'show' => ['Panoramica', route('projects.show', $project), 'bi-columns-gap'],
        'tasks' => ['Task', route('projects.tasks.index', $project), 'bi-kanban'],
        'calendar' => ['Calendario', route('projects.calendar', $project), 'bi-calendar3'],
        'members' => ['Membri', route('projects.members.index', $project), 'bi-people'],
    ] as $key => [$label, $href, $icon])
        <li class="nav-item">
            <a @class(['nav-link px-3 py-2 d-inline-flex align-items-center gap-2', 'active fw-semibold text-primary' => $tab === $key, 'text-secondary' => $tab !== $key]) href="{{ $href }}">
                <i class="bi {{ $icon }}"></i>
                <span>{{ $label }}</span>
            </a>
        </li>
    @endforeach
</ul>

{{-- Banner informativo di sola lettura (se il progetto è completato o archiviato) --}}
@if (! $project->isEditable())
    <div class="alert alert-secondary d-flex align-items-center justify-content-between gap-3 mb-4 py-2 px-3 small border" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-info-circle-fill text-secondary fs-6"></i>
            <span>
                Questo progetto è <strong>{{ $project->status->label() }}</strong> ed è in sola lettura.
                @can('manage', $project)
                    @if (($tab ?? null) === 'edit')
                        Per sbloccarlo e riprendere le modifiche, seleziona uno stato attivo nel riquadro «Stato del progetto».
                    @else
                        Per riprendere le modifiche o aggiungere task, riattivalo da <a href="{{ route('projects.edit', $project) }}" class="alert-link">Impostazioni</a>.
                    @endif
                @endcan
            </span>
        </div>
    </div>
@endif

<x-validation-errors />

