{{-- Schermata principale di dettaglio del progetto con iterazioni, bacheca sprint e statistiche --}}
@php
    use App\Enums\TaskStatus;

    $byStatus = $tasks->countBy(fn ($t) => $t->status->value);
    $byIteration = $tasks->groupBy('iteration_id');
@endphp

<x-app-layout :title="$project->name">
    @include('projects._header', ['tab' => 'show'])

    <div class="row g-4">
        {{-- Colonna sinistra: Descrizione progetto, avanzamento task e gestione iterazioni/sprint --}}
        <div class="col-lg-8 d-flex flex-column gap-4">
            {{-- Card 1: Obiettivo del progetto e barra di stato complessiva dei task --}}
            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="card-body p-4">
                    @if ($project->description)
                        <div class="mb-4">
                            <h2 class="h6 fw-bold text-dark mb-2">Obiettivo e Descrizione</h2>
                            <p class="text-body-secondary mb-0 lh-base">{{ $project->description }}</p>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between align-items-center small mb-3">
                        <span class="fw-bold text-dark"><i class="bi bi-pie-chart me-1 text-primary"></i>Avanzamento dei task</span>
                        <a href="{{ route('projects.tasks.index', $project) }}" class="text-primary text-decoration-none fw-semibold">
                            Apri backlog <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    @include('partials.status-bar', ['counts' => collect(TaskStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $byStatus[$s->value] ?? 0])])
                </div>
            </div>

            {{-- Card 2: Iterazioni & Sprint con avanzamento percentuale e form di inserimento/modifica --}}
            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-repeat text-primary fs-5"></i>
                        <h2 class="h6 fw-bold mb-0 text-dark">Iterazioni & Sprint</h2>
                    </div>
                    <span class="badge rounded-pill bg-light text-secondary border px-2 py-1 small">
                        {{ $project->iterations->count() }} iterazioni
                    </span>
                </div>
                <ul class="list-group list-group-flush border-0">
                    @forelse ($project->iterations as $iteration)
                        @php
                            $its = $byIteration[$iteration->id] ?? collect();
                            $done = $its->where('status', TaskStatus::Completed)->count();
                            $pct = $its->count() ? round($done / $its->count() * 100) : 0;
                            [$state, $badgeClass] = match (true) {
                                $iteration->ends_on->isPast() && ! $iteration->ends_on->isToday() => ['Conclusa', 'bg-light text-secondary border'],
                                $iteration->starts_on->isFuture() => ['Pianificata', 'bg-light text-primary border border-primary border-opacity-25'],
                                default => ['In corso', 'bg-primary text-white'],
                            };
                        @endphp
                        <li class="list-group-item px-4 py-3 border-bottom position-relative">
                            <div class="row g-2 align-items-center">
                                <div class="col-sm-4">
                                    <a class="fw-bold text-dark text-decoration-none hover-primary d-block stretched-link" href="{{ route('projects.tasks.index', [$project, 'iteration' => $iteration->id]) }}">{{ $iteration->name }}</a>
                                    <div class="small text-body-secondary">
                                        <i class="bi bi-calendar-range me-1"></i>{{ $iteration->starts_on->format('d/m') }} – {{ $iteration->ends_on->format('d/m/Y') }}
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <span class="badge rounded-pill fw-medium {{ $badgeClass }}">{{ $state }}</span>
                                </div>
                                <div class="col d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;" role="progressbar" aria-label="Completati {{ $iteration->name }}" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar bg-primary" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="small text-body-secondary text-nowrap fw-medium">{{ $done }}/{{ $its->count() }}</span>
                                </div>
                                @can('manage', $iteration)
                                    <div class="col-auto position-relative z-1">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-light border" data-bs-toggle="collapse" data-bs-target="#iteration-{{ $iteration->id }}" aria-expanded="false" title="Modifica"><i class="bi bi-pencil"></i></button>
                                            <form method="POST" action="{{ route('iterations.destroy', $iteration) }}" class="btn-group btn-group-sm" data-confirm="Eliminare l'iterazione «{{ $iteration->name }}»? I suoi task restano nel progetto, senza iterazione." data-confirm-button="Elimina">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-light border text-danger" title="Elimina"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                @endcan
                            </div>
                            @can('manage', $iteration)
                                <form method="POST" action="{{ route('iterations.update', $iteration) }}" class="collapse mt-3 p-3 bg-light rounded-3 border position-relative z-1" id="iteration-{{ $iteration->id }}">
                                    @csrf
                                    @method('PUT')
                                    @include('projects._iteration-fields', ['iteration' => $iteration, 'submit' => 'Salva'])
                                </form>
                            @endcan
                        </li>
                    @empty
                        <li class="list-group-item py-4 text-center border-0">
                            @include('partials.empty', [
                                'icon' => 'bi-arrow-repeat',
                                'title' => 'Nessuna iterazione',
                                'text' => 'Pianifica uno sprint o una fase di rilascio aggiungendo un\'iterazione.'
                            ])
                        </li>
                    @endforelse
                </ul>
                @can('update', $project)
                    <form method="POST" action="{{ route('projects.iterations.store', $project) }}" class="card-footer bg-light py-3 border-top">
                        @csrf
                        @include('projects._iteration-fields', ['iteration' => null, 'submit' => 'Aggiungi iterazione'])
                    </form>
                @endcan
            </div>
        </div>

        {{-- Colonna destra: Dettagli anagrafici del progetto e componenti del team --}}
        <div class="col-lg-4 d-flex flex-column gap-4">
            {{-- Card 3: Metadati del progetto (PM, stato, creatore, data di creazione) --}}
            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle text-primary"></i>
                    <h2 class="h6 fw-bold mb-0 text-dark">Dettagli progetto</h2>
                </div>
                <div class="card-body p-4 d-flex flex-column gap-3">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="d-flex align-items-center gap-3">
                            @if ($project->manager)
                                <x-avatar :user="$project->manager" size="lg" />
                            @else
                                <span class="badge rounded-pill bg-body-secondary text-body-secondary border p-3 fs-5"><i class="bi bi-person-dash"></i></span>
                            @endif
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="small text-body-secondary">Project Manager</div>
                                <div @class(['text-truncate', 'fw-semibold text-dark' => $project->manager, 'fst-italic text-body-secondary' => ! $project->manager])>
                                    {{ $project->manager?->full_name ?? 'Non assegnato' }}
                                    @if ($project->manager && ! $project->hasActiveManager())
                                        <span class="badge rounded-pill bg-light text-secondary border fw-medium ms-1">Disattivato</span>
                                    @endif
                                </div>
                            </div>
                            @can('assignManager', $project)
                                <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="collapse" data-bs-target="#pm-form" aria-controls="pm-form" aria-expanded="{{ $project->hasActiveManager() ? 'false' : 'true' }}" title="Cambia project manager"><i class="bi bi-pencil"></i></button>
                            @endcan
                        </div>
                        @can('assignManager', $project)
                            {{-- Aperto subito se manca un PM attivo o se l'invio precedente ha dato errore. --}}
                            @if ($managers->isNotEmpty())
                                <form method="POST" action="{{ route('projects.manager.update', $project) }}" id="pm-form" @class(['collapse', 'show' => ! $project->hasActiveManager() || $errors->has('pm_id')])>
                                    @csrf
                                    @method('PATCH')
                                    <div class="d-flex gap-2 mt-3 pt-3 border-top">
                                        <select name="pm_id" class="form-select form-select-sm" aria-label="Project manager" required>
                                            <option value="" disabled @selected(! $project->pm_id)>Scegli un project manager</option>
                                            @foreach ($managers as $manager)
                                                <option value="{{ $manager->id }}" @selected($project->pm_id === $manager->id)>{{ $manager->full_name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-primary text-nowrap"><i class="bi bi-check-lg me-1"></i>Assegna</button>
                                    </div>
                                    <x-input-error for="pm_id" />
                                </form>
                            @else
                                <div id="pm-form" @class(['collapse', 'show' => ! $project->hasActiveManager()])>
                                    <div class="alert alert-light border small text-body-secondary mt-3 mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-info-circle text-primary flex-shrink-0"></i>
                                        <span>Nessun Project Manager attivo registrato nel sistema.</span>
                                    </div>
                                </div>
                            @endif
                        @endcan
                    </div>
                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="text-body-secondary"><i class="bi bi-flag me-2"></i>Stato</span>
                        <x-status :value="$project->status" />
                    </div>
                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="text-body-secondary"><i class="bi bi-person me-2"></i>Creato da</span>
                        <span class="d-flex align-items-center gap-2 fw-medium text-dark">
                            <x-avatar :user="$project->creator" size="sm" />{{ $project->creator->full_name }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="text-body-secondary"><i class="bi bi-calendar3 me-2"></i>Creato il</span>
                        <span class="text-dark">{{ $project->created_at->format('d/m/Y') }}</span>
                    </div>
                    {{-- Progetto senza un PM attivo: l'organizer può concluderlo (se i task sono tutti completati) o archiviarlo, riattivarlo se è concluso o archiviato (per poi assegnare un nuovo PM); se non ha mai avuto un PM, anche eliminarlo. --}}
                    @can('manageOrphan', $project)
                        <div class="pt-3 border-top d-flex flex-column gap-2">
                            @if (! $project->isEditable())
                                <form method="POST" action="{{ route('projects.status.update', $project) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ \App\Enums\ProjectStatus::InProgress->value }}">
                                    <button class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-arrow-counterclockwise me-1"></i>Riattiva progetto</button>
                                </form>
                            @elseif ($tasks->isNotEmpty() && ($byStatus[TaskStatus::Completed->value] ?? 0) === $tasks->count())
                                <form method="POST" action="{{ route('projects.status.update', $project) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ \App\Enums\ProjectStatus::Finished->value }}">
                                    <button class="btn btn-sm btn-outline-{{ \App\Enums\ProjectStatus::Finished->color() }} w-100"><i class="bi bi-check2-circle me-1"></i>Concludi progetto</button>
                                </form>
                            @endif
                            @unless ($project->isArchived())
                                <form method="POST" action="{{ route('projects.status.update', $project) }}"
                                      data-confirm="Archiviare il progetto «{{ $project->name }}»? Non comparirà più nell'elenco dei progetti attivi." data-confirm-button="Archivia">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ \App\Enums\ProjectStatus::Archived->value }}">
                                    <button class="btn btn-sm btn-outline-secondary w-100"><i class="bi bi-archive me-1"></i>Archivia progetto</button>
                                </form>
                            @endunless
                            @can('delete', $project)
                                <form method="POST" action="{{ route('projects.destroy', $project) }}"
                                      data-confirm="Eliminare definitivamente il progetto «{{ $project->name }}» con tutti i suoi task, commenti e iterazioni?" data-confirm-button="Elimina">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash me-1"></i>Elimina progetto</button>
                                </form>
                            @endcan
                        </div>
                    @endcan
                </div>
            </div>

            {{-- Card 4: Gruppo di lavoro con link rapido alla schermata di gestione membri --}}
            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-people text-primary"></i>
                        <h2 class="h6 fw-bold mb-0 text-dark">Gruppo di lavoro</h2>
                    </div>
                    <a class="small text-primary text-decoration-none fw-semibold" href="{{ route('projects.members.index', $project) }}">
                        {{ auth()->user()->can('manage', $project) ? 'Gestisci' : 'Tutti (' . $project->members->count() . ')' }}
                    </a>
                </div>
                <ul class="list-group list-group-flush border-0">
                    @forelse ($project->members as $member)
                        <li class="list-group-item d-flex align-items-center gap-3 px-4 py-2 border-bottom">
                            <x-avatar :user="$member" size="sm" />
                            <div class="small overflow-hidden">
                                <div class="fw-semibold text-dark text-truncate">{{ $member->full_name }}</div>
                                <div class="text-body-secondary text-truncate">{{ $member->teams->pluck('name')->join(', ') ?: 'Nessun team' }}</div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item py-4 text-center border-0">
                            @include('partials.empty', [
                                'icon' => 'bi-people',
                                'title' => 'Nessun membro',
                                'text' => 'Non ci sono membri assegnati a questo progetto.'
                            ])
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
