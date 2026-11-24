{{-- Bacheca Kanban e tabella delle attività del progetto raggruppate per stato --}}
@php
    use App\Enums\Priority;
    use App\Enums\TaskStatus;

    $kanban = request('view') === 'kanban';
    $user = auth()->user();
    $groups = $project->iterations
        ->map(fn ($i) => ['name' => $i->name, 'range' => $i->starts_on->format('d/m') . ' – ' . $i->ends_on->format('d/m/Y'), 'tasks' => $tasks->where('iteration_id', $i->id)])
        ->push(['name' => 'Senza iterazione', 'range' => '', 'tasks' => $tasks->whereNull('iteration_id')])
        ->filter(fn ($g) => $g['tasks']->isNotEmpty());
@endphp

<x-app-layout :title="'Task · ' . $project->name">
    {{-- Intestazione del progetto con tab attivo sui task --}}
    @include('projects._header', ['tab' => 'tasks'])

    {{-- Selettore della modalità di visualizzazione: Backlog o Kanban --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="btn-group shadow-sm" role="group" aria-label="Visualizzazione">
            <a href="{{ request()->fullUrlWithQuery(['view' => null]) }}" @class([
                'btn btn-sm d-inline-flex align-items-center gap-1 px-3',
                'btn-primary active' => ! $kanban,
                'btn-light border text-secondary' => $kanban
            ])>
                <i class="bi bi-list-ul"></i><span>Backlog</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['view' => 'kanban']) }}" @class([
                'btn btn-sm d-inline-flex align-items-center gap-1 px-3',
                'btn-primary active' => $kanban,
                'btn-light border text-secondary' => ! $kanban
            ])>
                <i class="bi bi-kanban"></i><span>Kanban</span>
            </a>
        </div>
    </div>

    {{-- Modulo dei filtri di ricerca (stato, membro, priorità, iterazione) --}}
    <form method="GET" class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            @if ($kanban)<input type="hidden" name="view" value="kanban">@endif
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-medium mb-1 text-secondary" for="f-status">
                        <i class="bi bi-funnel me-1"></i>Stato
                    </label>
                    <select id="f-status" name="status" class="form-select form-select-sm">
                        <option value="">Tutti gli stati</option>
                        @foreach (TaskStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-medium mb-1 text-secondary" for="f-assignee">
                        <i class="bi bi-person me-1"></i>Assegnatario
                    </label>
                    <select id="f-assignee" name="assignee" class="form-select form-select-sm">
                        <option value="">Tutti i membri</option>
                        @foreach ($project->members as $member)
                            <option value="{{ $member->id }}" @selected(($filters['assignee'] ?? null) == $member->id)>{{ $member->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-medium mb-1 text-secondary" for="f-priority">
                        <i class="bi bi-flag me-1"></i>Priorità
                    </label>
                    <select id="f-priority" name="priority" class="form-select form-select-sm">
                        <option value="">Tutte le priorità</option>
                        @foreach (Priority::cases() as $priority)
                            <option value="{{ $priority->value }}" @selected(($filters['priority'] ?? null) === $priority->value)>{{ $priority->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-medium mb-1 text-secondary" for="f-iteration">
                        <i class="bi bi-arrow-repeat me-1"></i>Iterazione
                    </label>
                    <select id="f-iteration" name="iteration" class="form-select form-select-sm">
                        <option value="">Tutte le iterazioni</option>
                        @foreach ($project->iterations as $iteration)
                            <option value="{{ $iteration->id }}" @selected(($filters['iteration'] ?? null) == $iteration->id)>{{ $iteration->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-sm btn-primary shadow-sm">
                        <i class="bi bi-search me-1"></i>Filtra
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- Stato vuoto se nessun task corrisponde ai criteri di ricerca --}}
    @if ($tasks->isEmpty())
        @include('partials.empty', [
            'icon' => 'bi-list-check',
            'title' => 'Nessun task trovato',
            'text' => $filters ? 'Nessun task corrisponde ai filtri impostati.' : 'Non ci sono ancora task in questo progetto.',
            'cta' => auth()->user()->can('manage', $project) ? 'Crea il primo task' : null,
            'ctaHref' => auth()->user()->can('manage', $project) ? route('projects.tasks.create', $project) : null
        ])
    {{-- Vista a Bacheca Kanban: colonne verticali suddivise per stato del task --}}
    @elseif ($kanban)
        <div class="row g-3">
            @foreach (TaskStatus::cases() as $status)
                @php $column = $tasks->where('status', $status); @endphp
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="kanban-col bg-light rounded-3 p-2 h-100 border" style="border-top: 3px solid var(--bs-{{ $status->color() }}) !important;">
                        <div class="d-flex align-items-center justify-content-between px-2 py-2 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-circle-fill small text-{{ $status->color() }}"></i>
                                <span class="fw-bold small text-uppercase text-dark">{{ $status->label() }}</span>
                            </div>
                            <span class="badge rounded-pill bg-white text-dark border px-2 py-1 small fw-semibold">
                                {{ $column->count() }}
                            </span>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            @forelse ($column as $task)
                                @php $mine = $task->isAssignedTo($user); @endphp
                                <a href="{{ route('tasks.show', $task) }}" @class([
                                    'card text-decoration-none shadow-sm border-0 position-relative',
                                    'border-start border-primary border-3' => $mine
                                ]) style="transition: transform 0.15s ease, box-shadow 0.15s ease;">
                                    <div class="card-body p-3 d-flex flex-column gap-2">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <span class="fw-semibold text-dark line-clamp-2">{{ $task->title }}</span>
                                            <x-status :value="$task->priority" :pill="false" class="align-self-start flex-shrink-0" />
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between small text-body-secondary pt-2 border-top mt-1">
                                            <span class="d-inline-flex align-items-center gap-1 {{ $task->isOverdue() ? 'text-danger fw-semibold' : '' }}">
                                                <i class="bi bi-calendar3"></i>@include('tasks._range')
                                            </span>
                                            <div class="d-flex align-items-center avatar-stack">
                                                @foreach ($task->assignees as $assignee)
                                                    <x-avatar :user="$assignee" size="sm" />
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="text-center py-4 border border-dashed rounded-3 text-body-secondary small">
                                    Nessun task in questa fase
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    {{-- Vista a Backlog: tabella delle attività raggruppate per iterazione/sprint --}}
    @else
        <div class="d-flex flex-column gap-4">
            @foreach ($groups as $group)
                <div class="card shadow-sm border-0 overflow-hidden">
                    <div class="card-header bg-transparent d-flex flex-wrap align-items-center gap-2 py-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-arrow-repeat text-primary"></i>
                            <h2 class="h6 fw-bold mb-0 text-dark">{{ $group['name'] }}</h2>
                        </div>
                        @if ($group['range'])
                            <span class="badge bg-light text-secondary border small">{{ $group['range'] }}</span>
                        @endif
                        <span class="badge rounded-pill bg-light text-dark border ms-auto">{{ $group['tasks']->count() }} task</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="small text-uppercase bg-light">
                                <tr>
                                    <th class="ps-3 py-3 text-secondary fw-semibold">Task</th>
                                    <th class="py-3 text-secondary fw-semibold">Stato</th>
                                    <th class="py-3 text-secondary fw-semibold">Priorità</th>
                                    <th class="py-3 text-secondary fw-semibold">Assegnatari</th>
                                    <th class="pe-3 py-3 text-end text-secondary fw-semibold">Periodo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($group['tasks'] as $task)
                                    @php $mine = $task->isAssignedTo($user); @endphp
                                    <tr @class(['position-relative', 'bg-primary bg-opacity-10' => $mine])>
                                        <td class="ps-3 py-3">
                                            <a class="fw-semibold text-dark text-decoration-none hover-primary stretched-link" href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a>
                                            @if ($mine)
                                                <span class="badge rounded-pill bg-primary text-white ms-2 px-2 py-1 small fw-medium">
                                                    <i class="bi bi-person-fill me-1"></i>Tuo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3"><x-status :value="$task->status" /></td>
                                        <td class="py-3"><x-status :value="$task->priority" :pill="false" /></td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center avatar-stack">
                                                @foreach ($task->assignees as $assignee)
                                                    <x-avatar :user="$assignee" size="sm" />
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="small text-nowrap pe-3 py-3 text-end {{ $task->isOverdue() ? 'text-danger fw-semibold' : 'text-body-secondary' }}">
                                            @include('tasks._range')
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
