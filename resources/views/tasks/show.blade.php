{{-- Dettaglio del singolo task con stato, assegnatari, date e discussione dei commenti --}}
@php
    use App\Enums\TaskStatus;

    $user = auth()->user();
    $flow = TaskStatus::cases();
    $current = array_search($task->status, $flow, true);
    $actions = [
        'in_progress' => $task->status === TaskStatus::InReview
            ? ['Rimanda in corso', 'bi-arrow-counterclockwise', 'btn-outline-secondary']
            : ['Inizia', 'bi-play-fill', 'btn-primary'],
        'in_review' => ['Invia in review', 'bi-send', 'btn-primary'],
        'completed' => ['Approva e completa', 'bi-check2-circle', 'btn-primary'],
    ];
    $transitions = $user->can('updateStatus', $task)
        ? array_filter($flow, fn ($next) => $task->status->canTransitionTo($next, $user->can('manage', $task)))
        : [];
    $assignedAt = fn ($a) => $a->pivot->assigned_at ? \Illuminate\Support\Carbon::parse($a->pivot->assigned_at)->format('d/m/Y') : null;
@endphp

<x-page :title="$task->title">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-decoration-none">Progetti</a></li>
        <li class="breadcrumb-item"><a href="{{ route('projects.show', $task->project) }}" class="text-decoration-none">{{ $task->project->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('projects.tasks.index', $task->project) }}" class="text-decoration-none">Task</a></li>
        <li class="breadcrumb-item active fw-medium text-dark" aria-current="page">{{ $task->title }}</li>
    </x-slot>
    @can('manage', $task)
        <x-slot name="actions">
            <a class="btn btn-light border shadow-sm" href="{{ route('tasks.edit', $task) }}"><i class="bi bi-pencil me-1"></i>Modifica</a>
        </x-slot>
    @endcan

    {{-- Avvisi di sistema: progetto archiviato/concluso (sola lettura) o ritardo sulla scadenza --}}
    @if (! $task->project->isEditable())
        <div class="alert alert-secondary d-flex align-items-center justify-content-between gap-2 shadow-sm rounded-3 mb-4 py-2 px-3 small border">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-info-circle-fill text-secondary fs-6"></i>
                <span>Il progetto di appartenenza è <strong>{{ $task->project->status->label() }}</strong>: questo task è in sola lettura.</span>
            </div>
        </div>
    @elseif ($task->isOverdue() && $task->status !== TaskStatus::Completed)
        <div class="alert alert-danger d-flex align-items-center gap-2 shadow-sm rounded-3 mb-4 py-2 px-3">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>
                <strong>Task in ritardo:</strong> la data di scadenza prevista ({{ $task->end_date?->format('d/m/Y') }}) è stata superata.
            </div>
        </div>
    @endif

    <div class="row g-4">
        {{-- Colonna sinistra: Flusso di avanzamento del workflow, descrizione e discussione --}}
        <div class="col-lg-8 d-flex flex-column gap-4">
            {{-- Stepper visivo dello stato e pulsanti per le transizioni consentite del workflow --}}
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-diagram-3 text-primary fs-5"></i>
                            <h2 class="h6 fw-bold mb-0 text-dark">Stato del workflow</h2>
                        </div>
                        @if ($task->isAssignedTo($user))
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 small fw-medium">
                                <i class="bi bi-person-fill me-1"></i>Assegnato a te
                            </span>
                        @endif
                    </div>

                    {{-- Visual Stepper --}}
                    <div class="d-flex align-items-center justify-content-between mb-4 px-2 overflow-x-auto py-2">
                        @foreach ($flow as $i => $step)
                            <div class="d-flex align-items-center flex-shrink-0">
                                <div class="d-flex flex-column align-items-center text-center">
                                    <div @class([
                                        'rounded-circle d-flex align-items-center justify-content-center mb-1',
                                        'bg-' . $step->color() . ' text-white shadow-sm' => $i === $current,
                                        'bg-success text-white' => $i < $current,
                                        'bg-light border text-secondary' => $i > $current
                                    ]) style="width: 32px; height: 32px; font-size: 0.85rem;">
                                        {{-- d-flex sull'icona: annulla il vertical-align di Bootstrap Icons, che la spinge sotto il centro. --}}
                                        <i class="bi {{ $i < $current ? 'bi-check2' : $step->icon() }} d-flex"></i>
                                    </div>
                                    <span @class([
                                        'small fw-medium text-nowrap',
                                        'fw-bold text-dark' => $i === $current,
                                        'text-secondary' => $i !== $current
                                    ]) style="font-size: 0.75rem;">
                                        {{ $step->label() }}
                                    </span>
                                </div>
                            </div>
                            @if (! $loop->last)
                                <div class="stepper-connector {{ $i < $current ? 'done bg-success' : 'bg-light border-top' }} mx-2" style="height: 2px; min-width: 30px;"></div>
                            @endif
                        @endforeach
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2 pt-3 border-top">
                        @forelse ($transitions as $next)
                            @php [$label, $icon, $class] = $actions[$next->value]; @endphp
                            <form method="POST" action="{{ route('tasks.status.update', $task) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $next->value }}">
                                <button class="btn btn-sm shadow-sm {{ $class }} d-inline-flex align-items-center gap-1">
                                    <i class="bi {{ $icon }}"></i>
                                    <span>{{ $label }}</span>
                                </button>
                            </form>
                        @empty
                            @if ($task->status === TaskStatus::Completed)
                                <div class="d-flex align-items-center gap-2 text-success small fw-medium">
                                    <i class="bi bi-check-circle-fill fs-5"></i> Questo task è stato completato con successo.
                                </div>
                            @elseif ($task->status === TaskStatus::InReview)
                                <div class="d-flex align-items-center gap-2 text-warning small fw-medium">
                                    <i class="bi bi-hourglass-split fs-5"></i> In attesa della revisione e approvazione del PM.
                                </div>
                            @endif
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Descrizione dettagliata dell'attività --}}
            @if ($task->description)
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="h6 fw-bold text-dark mb-2">Descrizione dell'attività</h2>
                        <div class="text-secondary lh-base">{{ $task->description }}</div>
                    </div>
                </div>
            @endif

            {{-- Sezione discussione e commenti sul task --}}
            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-chat-left-text text-primary"></i>
                        <h2 class="h6 fw-bold mb-0 text-dark">Discussione</h2>
                    </div>
                    <span class="badge rounded-pill bg-light text-secondary border px-2 py-1 small">
                        {{ $task->comments->count() }} commenti
                    </span>
                </div>
                @include('comments._list', ['comments' => $task->comments])
                @include('comments._form')
            </div>
        </div>

        {{-- Colonna destra: Dettagli sintetici dell'attività ed elenco assegnatari --}}
        <div class="col-lg-4 d-flex flex-column gap-4">
            {{-- Metadati del task (stato, priorità, iterazione, scadenze) --}}
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 d-flex flex-column gap-3 small">
                    <h2 class="h6 fw-bold text-dark mb-1">Dettagli attività</h2>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-body-secondary">Stato corrente</span>
                        <x-status :value="$task->status" />
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-body-secondary">Priorità</span>
                        <x-status :value="$task->priority" :pill="false" />
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-body-secondary">Iterazione</span>
                        <span class="fw-medium text-dark">{{ $task->iteration?->name ?? 'Nessuna (backlog)' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-body-secondary">Data inizio</span>
                        <span class="text-dark">{{ $task->start_date?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-body-secondary">Scadenza</span>
                        <span @class(['fw-bold text-danger' => $task->isOverdue() && $task->status !== TaskStatus::Completed, 'text-dark' => ! $task->isOverdue()])>
                            {{ $task->end_date?->format('d/m/Y') ?? '—' }}
                        </span>
                    </div>
                    @if ($task->completed_at)
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="text-body-secondary">Completato il</span>
                            <span class="text-success fw-medium">{{ $task->completed_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Elenco e gestione degli assegnatari del task con modulo di aggiunta rapida --}}
            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-people text-primary"></i>
                        <h2 class="h6 fw-bold mb-0 text-dark">Assegnatari</h2>
                    </div>
                    <span class="badge rounded-pill bg-light text-secondary border px-2 py-1 small">
                        {{ $task->assignees->count() }}
                    </span>
                </div>
                <ul class="list-group list-group-flush border-0">
                    @forelse ($task->assignees as $assignee)
                        <li class="list-group-item d-flex align-items-center gap-3 px-4 py-2 border-bottom">
                            <x-avatar :user="$assignee" size="sm" />
                            <div class="flex-grow-1 small overflow-hidden">
                                <div class="fw-semibold text-dark text-truncate">{{ $assignee->full_name }}</div>
                                @if ($since = $assignedAt($assignee))
                                    <div class="text-body-secondary" style="font-size: 0.75rem;">dal {{ $since }}</div>
                                @endif
                            </div>
                            @can('manage', $task)
                                <form method="POST" action="{{ route('tasks.assignees.destroy', [$task, $assignee]) }}" data-confirm="Togliere {{ $assignee->full_name }} dal task?" data-confirm-button="Togli">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-light border text-danger" title="Rimuovi assegnazione" aria-label="Rimuovi {{ $assignee->full_name }}">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            @endcan
                        </li>
                    @empty
                        <li class="list-group-item py-4 text-center border-0">
                            @include('partials.empty', [
                                'icon' => 'bi-person-dash',
                                'title' => 'Nessun assegnatario',
                                'text' => 'Assegna questo task a un membro del team.'
                            ])
                        </li>
                    @endforelse
                </ul>
                @can('manage', $task)
                    {{-- Ricerca (GET ?q=) e assegnazione rapida (POST) centralizzata tramite componente --}}
                    @if ($candidates->isNotEmpty() || filled(request()->query('q')))
                        <div class="card-footer bg-light p-3 border-top d-flex flex-column gap-2">
                            <div class="small fw-semibold text-dark d-flex align-items-center gap-1">
                                <i class="bi bi-person-plus text-primary"></i>Assegna collaboratore
                            </div>
                            <x-candidate-picker 
                                :action="route('tasks.assignees.store', $task)" 
                                :candidates="$candidates" 
                                button-label="Assegna" 
                                :show-teams="true"
                                max-height="200px" 
                                empty-text="Nessun membro del progetto trovato."
                            />
                        </div>
                    @else
                        <div class="card-footer bg-light py-2 px-3 border-top small text-body-secondary d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle text-primary flex-shrink-0"></i>
                            <span>{{ $hasMembers ? 'Tutti i membri del progetto sono già assegnati.' : 'Nessun membro assegnato al progetto.' }}</span>
                        </div>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</x-page>
