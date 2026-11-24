{{-- Dashboard amministrativa dell'organizer con metriche globali, statistiche sui team e stato del sistema --}}
<x-app-layout title="Dashboard">
    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h5 fw-bold text-dark mb-1">
                Ciao, {{ Auth::user()->name }} 👋
            </h2>
            <p class="small text-body-secondary mb-0">
                Ecco cosa sta succedendo oggi &bull; {{ now()->locale('it')->translatedFormat('l j F Y') }}
            </p>
        </div>
    </div>

    {{-- Schede statistiche: metriche aggregate su scala aziendale (progetti, utenti, task, ritardi) --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <x-stat label="Progetti attivi" :value="$projects->count()" icon="bi-kanban" color="primary" hint="Pianificati e in corso" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat label="Utenti attivi" :value="$activeUsers" icon="bi-people" color="info" :hint="$teams . ' team operativi'" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat label="Task aperti" :value="$openTasks" icon="bi-list-check" color="warning" hint="In lavorazione o review" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat label="Task in ritardo" :value="$overdue" icon="bi-exclamation-triangle" :color="$overdue ? 'danger' : 'secondary'" :hint="$overdue ? 'Richiede attenzione' : 'Tutti in orario'" />
        </div>
    </div>

    {{-- Sezione operativa a due colonne: avanzamento progetti a sinistra e bilanciamento carichi del team a destra --}}
    <div class="row g-4">
        {{-- Colonna sinistra (7/12): tabella dei progetti attivi con PM, percentuale di completamento e ritardi --}}
        <div class="col-xl-7">
            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-folder2-open text-primary fs-5"></i>
                        <h2 class="h6 fw-bold mb-0 text-dark">Progetti attivi</h2>
                    </div>
                    <a href="{{ route('projects.index') }}" class="small fw-semibold text-primary text-decoration-none">
                        Vedi tutti <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="table-responsive overflow-auto mh-card">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="small text-uppercase bg-light sticky-top">
                            <tr>
                                <th class="ps-3 py-3 text-secondary fw-semibold">Progetto</th>
                                <th class="py-3 text-secondary fw-semibold">Project Manager</th>
                                <th class="py-3 text-secondary fw-semibold" style="min-width: 150px;">Avanzamento</th>
                                <th class="pe-3 py-3 text-end text-secondary fw-semibold">Ritardi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projects as $project)
                                @php
                                    $done = $project->tasks->where('status', \App\Enums\TaskStatus::Completed)->count();
                                    $progress = $project->tasks->isEmpty() ? 0 : (int) round($done / $project->tasks->count() * 100);
                                    $late = $project->tasks->filter->isOverdue()->count();
                                @endphp
                                <tr class="position-relative">
                                    <td class="ps-3 py-3">
                                        <a class="fw-semibold text-dark text-decoration-none hover-primary d-block mb-1 stretched-link" href="{{ route('projects.show', $project) }}">
                                            {{ $project->name }}
                                        </a>
                                        <x-status :value="$project->status" />
                                    </td>
                                    <td class="py-3 small">
                                        @if ($project->manager)
                                            <div class="d-flex align-items-center gap-2">
                                                <x-avatar :user="$project->manager" size="sm" />
                                                <span class="fw-medium text-dark">{{ $project->manager->full_name }}</span>
                                            </div>
                                        @else
                                            <span class="text-body-secondary fst-italic">Non assegnato</span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center justify-content-between small text-body-secondary mb-1">
                                            <span>{{ $progress }}%</span>
                                            <span>{{ $done }}/{{ $project->tasks->count() }} task</span>
                                        </div>
                                        <div class="progress" style="height: 6px;" role="progressbar" aria-label="Completamento {{ $project->name }}" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar bg-primary" style="width: {{ $progress }}%"></div>
                                        </div>
                                    </td>
                                    <td class="pe-3 py-3 text-end">
                                        @if ($late)
                                            <span class="badge rounded-pill text-bg-danger fw-semibold px-2 py-1">
                                                <i class="bi bi-clock-history me-1"></i>{{ $late }}
                                            </span>
                                        @else
                                            <span class="badge rounded-pill bg-light text-secondary border fw-normal">
                                                <i class="bi bi-check me-1 text-success"></i>0
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        @include('partials.empty', [
                                            'icon' => 'bi-folder2-open',
                                            'title' => 'Nessun progetto attivo',
                                            'text' => 'Non ci sono progetti attivi al momento.'
                                        ])
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Colonna destra (5/12): bilanciamento carichi di lavoro (workload) con barre proporzionali per membro --}}
        <div class="col-xl-5">
            @php($max = max(1, $workload->max('open_tasks_count')))
            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart-line text-primary fs-5"></i>
                        <h2 class="h6 fw-bold mb-0 text-dark">Carico del team</h2>
                    </div>
                    <span class="badge rounded-pill bg-light text-secondary border px-2 py-1 small">
                        {{ $workload->count() }} membri
                    </span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush overflow-auto mh-card border-0">
                        @forelse ($workload as $member)
                            <li class="list-group-item px-4 py-3 border-bottom position-relative">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <x-avatar :user="$member" />
                                    <div class="me-auto overflow-hidden">
                                        <a class="fw-semibold text-dark text-decoration-none hover-primary text-truncate d-block stretched-link" href="{{ route('users.show', $member) }}">
                                            {{ $member->full_name }}
                                        </a>
                                        <div class="small text-body-secondary text-truncate">
                                            {{ $member->teams->pluck('name')->join(', ') ?: 'Nessun team' }}
                                        </div>
                                    </div>
                                    @if ($member->overdue_tasks_count)
                                        <span class="badge rounded-pill text-bg-danger fw-medium" title="Task in ritardo">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $member->overdue_tasks_count }}
                                        </span>
                                    @endif
                                    <span class="badge rounded-pill bg-light text-dark border fw-medium px-2 py-1">
                                        {{ $member->open_tasks_count }} task
                                    </span>
                                </div>
                                <div class="progress" style="height: 6px;" role="progressbar" aria-label="Task aperti di {{ $member->full_name }}"
                                     aria-valuenow="{{ $member->open_tasks_count }}" aria-valuemin="0" aria-valuemax="{{ $max }}">
                                    <div class="progress-bar bg-primary" style="width: {{ $member->open_tasks_count / $max * 100 }}%"></div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item py-5 text-center border-0">
                                @include('partials.empty', [
                                    'icon' => 'bi-people',
                                    'title' => 'Nessun membro trovato',
                                    'text' => 'Non ci sono membri con carico di lavoro assegnato.'
                                ])
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
