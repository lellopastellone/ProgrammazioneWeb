{{-- Dashboard personale del membro/sviluppatore con i propri task assegnati e le prossime scadenze --}}

@php
    use App\Enums\TaskStatus;

    $open = $tasks->whereIn('status', [TaskStatus::Todo, TaskStatus::InProgress])->count();
    $inReview = $tasks->where('status', TaskStatus::InReview)->count();
    $overdue = $tasks->filter->isOverdue()->count();
    $completed = $tasks->where('status', TaskStatus::Completed)->count();
@endphp

<x-app-layout title="Dashboard">

    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h5 fw-bold text-dark mb-1">
                Ciao, {{ Auth::user()->name }} 👋
            </h2>
            <p class="small text-body-secondary mb-0">
                La tua lavagna di lavoro &bull; {{ now()->locale('it')->translatedFormat('l j F Y') }}
            </p>
        </div>
    </div>

    {{-- Schede statistiche: riepilogo dello stato dei task assegnati al membro --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <x-stat label="Da fare o in corso" :value="$open" icon="bi-list-task" color="primary" hint="Nel tuo backlog" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat label="In review" :value="$inReview" icon="bi-eye" color="warning" hint="In attesa del PM" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat label="In ritardo" :value="$overdue" icon="bi-exclamation-triangle" :color="$overdue ? 'danger' : 'secondary'" :hint="$overdue ? 'Priorità immediata' : 'Tutto in regola'" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat label="Completati" :value="$completed" icon="bi-check2-circle" color="success" hint="Task conclusi con successo" />
        </div>
    </div>

    {{-- Sezione operativa a due colonne: tabella dei task a sinistra e widget calendario/progetti a destra --}}
    <div class="row g-4">
        {{-- Colonna sinistra (8/12): elenco completo dei task assegnati con stato, priorità e scadenza --}}
        <div class="col-xl-8">
            @if ($tasks->isEmpty())
                @include('partials.empty', [
                    'icon' => 'bi-inbox',
                    'title' => 'Nessun task assegnato',
                    'text' => 'Ottimo lavoro! Al momento non hai attività assegnate in sospeso.'
                ])
            @else
                <div class="card shadow-sm border-0 overflow-hidden">
                    <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check2-square text-primary fs-5"></i>
                            <h2 class="h6 fw-bold mb-0 text-dark">I miei task</h2>
                        </div>
                        <span class="badge rounded-pill bg-light text-secondary border px-2 py-1 small">
                            {{ $tasks->count() }} totali
                        </span>
                    </div>
                    <div class="table-responsive overflow-auto mh-card">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="small text-uppercase bg-light sticky-top">
                                <tr>
                                    <th class="ps-3 py-3 text-secondary fw-semibold">Task</th>
                                    <th class="py-3 text-secondary fw-semibold">Progetto</th>
                                    <th class="py-3 text-secondary fw-semibold">Stato</th>
                                    <th class="py-3 text-secondary fw-semibold">Priorità</th>
                                    <th class="pe-3 py-3 text-end text-secondary fw-semibold">Scadenza</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $task)
                                    @php $late = $task->isOverdue(); @endphp
                                    <tr @class(['position-relative', 'opacity-75' => $task->status === TaskStatus::Completed])>
                                        <td class="ps-3 py-3">
                                            <a class="fw-semibold text-dark text-decoration-none hover-primary d-block stretched-link" href="{{ route('tasks.show', $task) }}">
                                                {{ $task->title }}
                                            </a>
                                        </td>
                                        <td class="py-3 small text-body-secondary">
                                            <span class="d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-folder2 text-secondary"></i>
                                                {{ $task->project->name }}
                                            </span>
                                        </td>
                                        <td class="py-3"><x-status :value="$task->status" /></td>
                                        <td class="py-3"><x-status :value="$task->priority" :pill="false" /></td>
                                        <td @class(['pe-3 py-3 text-end small text-nowrap', 'text-danger fw-semibold' => $late, 'text-body-secondary' => ! $late])>
                                            @if ($late)
                                                <i class="bi bi-exclamation-circle-fill me-1"></i>
                                            @endif
                                            {{ $task->end_date?->format('d/m/Y') ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- Colonna destra (4/12): mini-calendario del mese e lista dei progetti attivi --}}
        <div class="col-xl-4 d-flex flex-column gap-4">
            {{-- Anteprima del mese: tutta la card porta al calendario completo. --}}
            <div class="card shadow-sm border-0 overflow-hidden position-relative">
                <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-3 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar3 text-primary fs-5"></i>
                        <h2 class="h6 fw-bold mb-0 text-capitalize text-dark">{{ now()->locale('it')->translatedFormat('F Y') }}</h2>
                    </div>
                    <a class="small fw-semibold text-primary text-decoration-none stretched-link" href="{{ route('calendar') }}">
                        Apri calendario <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-3">
                    <x-calendar :month="now()" :tasks="$tasks" compact />
                </div>
            </div>

            {{-- Elenco compatto dei progetti attivi a cui il membro partecipa con rispettivo PM --}}
            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-kanban text-primary fs-5"></i>
                        <h2 class="h6 fw-bold mb-0 text-dark">I miei progetti</h2>
                    </div>
                    <span class="badge rounded-pill bg-light text-secondary border px-2 py-1 small">
                        {{ $projects->count() }}
                    </span>
                </div>
                <ul class="list-group list-group-flush overflow-auto mh-card border-0">
                    @forelse ($projects as $project)
                        <li class="list-group-item px-3 py-3 border-bottom position-relative">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                <a class="fw-semibold text-dark text-decoration-none hover-primary stretched-link" href="{{ route('projects.show', $project) }}">
                                    {{ $project->name }}
                                </a>
                                <x-status :value="$project->status" class="flex-shrink-0" />
                            </div>
                            <div class="small text-body-secondary mt-1 d-flex justify-content-between align-items-center">
                                <span>PM: {{ $project->manager?->full_name ?? 'Non assegnato' }}</span>
                                <span class="badge bg-light text-secondary border">{{ $project->tasks_count }} task</span>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item py-4 text-center border-0">
                            @include('partials.empty', [
                                'icon' => 'bi-folder2',
                                'title' => 'Nessun progetto',
                                'text' => 'Non fai ancora parte di nessun progetto.'
                            ])
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
