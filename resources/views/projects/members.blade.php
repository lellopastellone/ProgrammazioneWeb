{{-- Gestione dei membri e del team assegnati al progetto --}}
<x-app-layout :title="'Membri · ' . $project->name">
    @include('projects._header', ['tab' => 'members'])

    <div class="row g-4">
        {{-- Colonna sinistra: Tabella riassuntiva dei membri assegnati e del Project Manager --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-people text-primary fs-5"></i>
                        <h2 class="h6 fw-bold mb-0 text-dark">Componenti del gruppo</h2>
                    </div>
                    <span class="badge rounded-pill bg-light text-secondary border px-2 py-1 small">
                        {{ $members->count() + ($project->manager ? 1 : 0) }} totali
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="small text-uppercase bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary fw-semibold">Membro</th>
                                <th class="py-3 text-secondary fw-semibold">Team</th>
                                <th class="py-3 text-secondary fw-semibold">Task assegnati</th>
                                <th class="pe-4 py-3 text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Riga dedicata al Project Manager responsabile del progetto --}}
                            @if ($project->manager)
                                <tr class="bg-primary bg-opacity-10 position-relative">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <x-avatar :user="$project->manager" />
                                            <div>
                                                @role('organizer')
                                                    <a class="fw-bold text-dark text-decoration-none hover-primary stretched-link d-block" href="{{ route('users.show', $project->manager) }}">
                                                        {{ $project->manager->full_name }}
                                                    </a>
                                                @else
                                                    <div class="fw-bold text-dark">{{ $project->manager->full_name }}</div>
                                                @endrole
                                                <span class="badge bg-primary text-white rounded-pill fw-medium" style="font-size: 0.7rem;">
                                                    <i class="bi bi-briefcase me-1"></i>Project Manager
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 small text-body-secondary">—</td>
                                    <td class="py-3">
                                        <span class="badge bg-light text-secondary border">{{ $project->manager->tasks_count ?? '—' }}</span>
                                    </td>
                                    <td class="pe-4 py-3 text-end"></td>
                                </tr>
                            @endif
                            {{-- Collaboratori e membri del team assegnati al progetto --}}
                            @forelse ($members as $member)
                                <tr class="position-relative">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <x-avatar :user="$member" />
                                            <div>
                                                @role('organizer')
                                                    <a class="fw-semibold text-dark text-decoration-none hover-primary stretched-link d-block" href="{{ route('users.show', $member) }}">
                                                        {{ $member->full_name }}
                                                    </a>
                                                    <div class="small text-body-secondary">{{ $member->email }}</div>
                                                @else
                                                    <div class="fw-semibold text-dark">{{ $member->full_name }}</div>
                                                @endrole
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex flex-wrap gap-1">
                                            @forelse ($member->teams as $team)
                                                <span class="badge rounded-pill bg-light text-secondary border fw-medium">{{ $team->name }}</span>
                                            @empty
                                                <span class="small text-body-secondary">—</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge rounded-pill bg-light text-dark border fw-medium px-2 py-1">
                                            {{ $member->tasks_count }} task
                                        </span>
                                    </td>
                                    <td class="text-end pe-4 py-3 position-relative z-1">
                                        @can('update', $project)
                                            <form method="POST" action="{{ route('projects.members.destroy', [$project, $member]) }}" data-confirm="Rimuovere {{ $member->full_name }} dal gruppo di progetto?" data-confirm-button="Rimuovi">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-light border text-danger" title="Rimuovi dal gruppo">
                                                    <i class="bi bi-person-dash me-1"></i>Rimuovi
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        @include('partials.empty', [
                                            'icon' => 'bi-people',
                                            'title' => 'Nessun membro assegnato',
                                            'text' => 'Aggiungi collaboratori per iniziare ad assegnare compiti.'
                                        ])
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- Colonna destra: Modulo per l'assegnazione di nuovi collaboratori (riservato al PM) --}}
        <div class="col-lg-4">
            @can('manage', $project)
                @can('update', $project)
                    {{-- Card con ricerca (GET ?q=) e aggiunta rapida (POST) centralizzata tramite componente --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4 d-flex flex-column gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-person-plus text-primary fs-5"></i>
                                <h2 class="h6 fw-bold mb-0 text-dark">Aggiungi membro</h2>
                            </div>
                            <p class="small text-body-secondary mb-0">Cerca e seleziona un collaboratore per integrarlo nel team del progetto.</p>
                            <x-candidate-picker 
                                :action="route('projects.members.store', $project)"
                                :candidates="$candidates"
                                button-label="Aggiungi"
                                :show-teams="true"
                                empty-text="Tutti i collaboratori attivi fanno già parte di questo progetto."
                            />
                        </div>
                    </div>
                @else
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4 text-center">
                            <i class="bi bi-lock text-secondary fs-1 mb-2 d-block"></i>
                            <h2 class="h6 fw-bold text-dark mb-1">Gruppo non modificabile</h2>
                            <p class="small text-body-secondary mb-0">Il progetto è {{ strtolower($project->status->label()) }}. Riattivalo dalle impostazioni per aggiungere o rimuovere membri.</p>
                        </div>
                    </div>
                @endcan
            @endcan
        </div>
    </div>
</x-app-layout>
