{{-- Elenco completo dei progetti con schede di avanzamento, statistiche e filtri per stato --}}

{{-- Raggruppamento dei progetti per macro-stato (Attivi, Conclusi, Archiviati) e definizione del tab attivo --}}
@php
    use App\Enums\ProjectStatus;

    $user = auth()->user();
    // Tre tab: attivi (pianificati e in corso), conclusi, archiviati; la fase precisa sta nel badge della card.
    $tabs = ['active' => 'Attivi', 'finished' => 'Conclusi', 'archived' => 'Archiviati'];
    $filter = in_array(request('status'), array_keys($tabs), true) ? request('status') : 'active';
    $groups = $projects->groupBy(fn ($p) => in_array($p->status, ProjectStatus::active(), true) ? 'active' : $p->status->value);
    $shown = $groups->get($filter, collect());
    $subtitle = $user->can('projects.view-all') ? 'Tutti i progetti dell’organizzazione a cui hai accesso.'
        : ($user->hasRole('project_manager') ? 'I progetti che gestisci o di cui fai parte.' : 'I progetti del tuo gruppo di lavoro.');
@endphp

<x-page title="Progetti" :subtitle="$subtitle">
    {{-- Pulsante per la creazione di un nuovo progetto (visibile solo se autorizzati) --}}
    @can('projects.create')
        <x-slot name="actions">
            <a class="btn btn-primary shadow-sm" href="{{ route('projects.create') }}">
                <i class="bi bi-plus-lg me-1"></i>Nuovo progetto
            </a>
        </x-slot>
    @endcan

    {{-- Barra dei filtri a pillole (Attivi, Conclusi, Archiviati) con contatori in tempo reale --}}
    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom overflow-x-auto">
        <ul class="nav nav-pills gap-2 flex-nowrap">
            @foreach ($tabs as $key => $label)
                <li class="nav-item">
                    <a @class([
                        'nav-link py-1 px-3 rounded-pill text-nowrap small fw-medium',
                        'active bg-primary text-white shadow-sm' => $filter === $key,
                        'bg-white text-secondary border' => $filter !== $key
                    ]) href="{{ route('projects.index', $key === 'active' ? [] : ['status' => $key]) }}">
                        {{ $label }} <span class="badge rounded-pill bg-light text-dark ms-1">{{ $groups->get($key)?->count() ?? 0 }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Stato vuoto se non sono presenti progetti per il filtro selezionato --}}
    @if ($shown->isEmpty())
        @include('partials.empty', [
            'icon' => 'bi-folder2-open',
            'title' => 'Nessun progetto trovato',
            'text' => $filter === 'active' ? 'Non ci sono progetti attivi. Inizia creandone uno nuovo!' : 'Nessun progetto in questa sezione.',
            'cta' => auth()->user()->can('projects.create') ? 'Crea il primo progetto' : null,
            'ctaHref' => auth()->user()->can('projects.create') ? route('projects.create') : null
        ])
    @else
        {{-- Griglia responsive delle schede di progetto --}}
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
            @foreach ($shown as $project)
                @php $pct = $project->tasks_count ? round($project->completed_tasks_count / $project->tasks_count * 100) : 0; @endphp
                <div class="col">
                    <div @class([
                        'card h-100 shadow-sm border-0 overflow-hidden position-relative',
                        'opacity-75' => $project->status === ProjectStatus::Archived
                    ]) style="transition: transform 0.2s ease, box-shadow 0.2s ease;">
                        <div class="card-body p-4 d-flex flex-column gap-3">
                            {{-- Intestazione della card: Titolo con stretched-link e badge di stato --}}
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <a class="h5 mb-0 fw-bold text-dark text-decoration-none hover-primary stretched-link line-clamp-2" href="{{ route('projects.show', $project) }}">
                                    {{ $project->name }}
                                </a>
                                <x-status :value="$project->status" class="flex-shrink-0" />
                            </div>
                            
                            {{-- Breve descrizione del progetto con troncamento a 3 righe --}}
                            <p class="small text-body-secondary mb-0 line-clamp-3" style="min-height: 2.8rem;">
                                {{ $project->description ?: 'Nessuna descrizione specificata per questo progetto.' }}
                            </p>
                            
                            {{-- Avanzamento dei task con percentuale e progress bar accessibile --}}
                            <div class="mt-auto pt-2">
                                <div class="d-flex justify-content-between align-items-center small mb-1">
                                    <span class="text-body-secondary">
                                        <i class="bi bi-check2-circle me-1 text-success"></i>{{ $project->completed_tasks_count }} di {{ $project->tasks_count }} task
                                    </span>
                                    <span class="fw-bold text-dark">{{ $pct }}%</span>
                                </div>
                                <div class="progress" style="height: 6px;" role="progressbar" aria-label="Avanzamento {{ $project->name }}" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-primary" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        </div>
                        {{-- Footer della card: Responsabile (PM) e stack avatar dei membri del team --}}
                        <div class="card-footer bg-light bg-opacity-50 border-top py-2 px-4 d-flex align-items-center justify-content-between small">
                            <span class="text-body-secondary d-inline-flex align-items-center gap-1">
                                <i class="bi bi-person-badge text-secondary"></i>
                                <span>PM: {{ $project->manager?->full_name ?? '—' }}</span>
                            </span>
                            <div class="d-flex align-items-center avatar-stack">
                                @foreach ($project->members->take(4) as $member)
                                    <x-avatar :user="$member" size="sm" />
                                @endforeach
                                @if ($project->members->count() > 4)
                                    <span class="badge rounded-pill bg-secondary text-white" style="margin-left: -8px; border: 2px solid #fff; font-size: 0.65rem;">
                                        +{{ $project->members->count() - 4 }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-page>
