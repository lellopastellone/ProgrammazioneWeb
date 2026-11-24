{{-- Dashboard operativa dedicata al Project Manager con avanzamento progetti, sprint e carichi di lavoro --}}
<x-app-layout title="Dashboard">
    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h5 fw-bold text-dark mb-1">
                Ciao, {{ Auth::user()->name }} 👋
            </h2>
            <p class="small text-body-secondary mb-0">
                Panoramica progetti &bull; {{ now()->locale('it')->translatedFormat('l j F Y') }}
            </p>
        </div>
    </div>

    {{-- Schede statistiche: panoramica rapida su progetti, task aperti, review e ritardi --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <x-stat label="Progetti gestiti" :value="$projects->count()" icon="bi-kanban" color="primary" hint="Sotto la tua guida" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat label="Task aperti" :value="$openTasks" icon="bi-list-check" color="info" hint="Nel workflow corrente" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat label="Da revisionare" :value="$toReview->count()" icon="bi-eye" color="warning" :hint="$toReview->count() ? 'In attesa di approvazione' : 'Nessuna review in sospeso'" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat label="Task in ritardo" :value="$overdue" icon="bi-exclamation-triangle" :color="$overdue ? 'danger' : 'secondary'" :hint="$overdue ? 'Attività scadute' : 'Tutti in regola'" />
        </div>
    </div>

    {{-- Sezione operativa a due colonne: progetti coordinati a sinistra e task da revisionare a destra --}}
    <div class="row g-4">
        {{-- Colonna sinistra (8/12): tabella dei progetti gestiti con membri e barra di avanzamento dei task --}}
        <div class="col-xl-8">
            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-grid text-primary fs-5"></i>
                        <h2 class="h6 fw-bold mb-0 text-dark">I tuoi progetti</h2>
                    </div>
                    <span class="badge rounded-pill bg-light text-secondary border px-2 py-1 small">
                        {{ $projects->count() }} attivi
                    </span>
                </div>
                <div class="table-responsive overflow-auto mh-card">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="small text-uppercase bg-light sticky-top">
                            <tr>
                                <th class="ps-3 py-3 text-secondary fw-semibold">Progetto</th>
                                <th class="py-3 text-secondary fw-semibold">Stato</th>
                                <th class="py-3 text-secondary fw-semibold">Team</th>
                                <th class="pe-3 py-3 text-secondary fw-semibold" style="min-width: 200px;">Task per stato</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projects as $project)
                                <tr class="position-relative">
                                    <td class="ps-3 py-3">
                                        <a class="fw-semibold text-dark text-decoration-none hover-primary d-block stretched-link" href="{{ route('projects.show', $project) }}">
                                            {{ $project->name }}
                                        </a>
                                    </td>
                                    <td class="py-3"><x-status :value="$project->status" /></td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-1 text-body-secondary small">
                                            <i class="bi bi-people"></i>
                                            <span class="fw-medium">{{ $project->members_count }}</span>
                                        </div>
                                    </td>
                                    <td class="pe-3 py-3">
                                        @include('partials.status-bar', ['counts' => collect(\App\Enums\TaskStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $project->{$s->value . '_count'}])])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        @include('partials.empty', [
                                            'icon' => 'bi-kanban',
                                            'title' => 'Nessun progetto assegnato',
                                            'text' => 'Non gestisci ancora nessun progetto attivo.'
                                        ])
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Colonna destra (4/12): coda dei task in stato InReview in attesa di approvazione da parte del PM --}}
        <div class="col-xl-4">
            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-patch-check text-warning fs-5"></i>
                        <h2 class="h6 fw-bold mb-0 text-dark">Da revisionare</h2>
                    </div>
                    @if ($toReview->count())
                        <span class="badge rounded-pill text-bg-warning px-2 py-1 small fw-semibold">
                            {{ $toReview->count() }} da approvare
                        </span>
                    @else
                        <span class="badge rounded-pill bg-light text-secondary border px-2 py-1 small">
                            0
                        </span>
                    @endif
                </div>
                <ul class="list-group list-group-flush overflow-auto mh-card border-0">
                    @forelse ($toReview as $task)
                        <li class="list-group-item px-3 py-3 border-bottom position-relative">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                <a class="fw-semibold text-dark text-decoration-none hover-primary stretched-link line-clamp-2" href="{{ route('tasks.show', $task) }}">
                                    {{ $task->title }}
                                </a>
                                <x-status :value="$task->priority" :pill="false" class="small flex-shrink-0" />
                            </div>
                            <div class="small d-flex justify-content-between align-items-center text-body-secondary mt-2">
                                <span class="text-truncate me-2">
                                    <i class="bi bi-folder me-1"></i>{{ $task->project->name }}
                                </span>
                                @if ($task->end_date)
                                    <span class="text-nowrap {{ $task->isOverdue() ? 'text-danger fw-semibold' : '' }}">
                                        <i class="bi bi-calendar-event me-1"></i>{{ $task->end_date->format('d/m') }}
                                    </span>
                                @endif
                            </div>
                            <div class="small text-body-secondary mt-1">
                                <i class="bi bi-person me-1"></i>{{ $task->assignees->pluck('name')->join(', ') ?: 'Nessun assegnatario' }}
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item py-5 text-center border-0">
                            @include('partials.empty', [
                                'icon' => 'bi-check2-circle',
                                'title' => 'Tutto in pari!',
                                'text' => 'Nessun task è in attesa di revisione.'
                            ])
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
