{{-- Scheda di riepilogo del profilo utente con ruoli, team di appartenenza e progetti correlati --}}
<x-page :title="$user->full_name" :subtitle="$user->email">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none">Utenti</a></li>
        <li class="breadcrumb-item active text-dark fw-medium" aria-current="page">{{ $user->full_name }}</li>
    </x-slot>
    @can('edit', $user)
        <x-slot name="actions">
            <a class="btn btn-light border shadow-sm" href="{{ $user->is(auth()->user()) ? route('profile.show') : route('users.edit', $user) }}">
                <i class="bi bi-pencil me-1"></i>Modifica profilo
            </a>
        </x-slot>
    @endcan

    <div class="row g-4">
        {{-- Colonna sinistra: Scheda anagrafica dell'utente con ruoli, team e stato account --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 text-center border-bottom">
                    <x-avatar :user="$user" size="lg" class="mb-3" />
                    <h2 class="h5 fw-bold text-dark mb-1">{{ $user->full_name }}</h2>
                    <p class="text-secondary small mb-3">{{ $user->email }}</p>
                    <div class="d-flex justify-content-center">
                        @if ($user->active)
                            <span class="badge rounded-pill text-bg-success px-3 py-1 fw-medium">
                                <i class="bi bi-check-circle me-1"></i>Account attivo
                            </span>
                        @else
                            <span class="badge rounded-pill bg-light text-secondary border px-3 py-1 fw-medium">
                                <i class="bi bi-dash-circle me-1"></i>Disattivato
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4 d-flex flex-column gap-3 small">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-body-secondary">Ruoli assegnati</span>
                        <span class="d-flex flex-wrap gap-1 justify-content-end">@include('partials.role-badges')</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-body-secondary">Team di appartenenza</span>
                        <span class="fw-medium text-dark">{{ $user->teams->pluck('name')->join(', ') ?: 'Nessun team' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-body-secondary">Membro dal</span>
                        <span class="text-dark">{{ $user->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Colonna destra: Progetti correlati (sia gestiti come PM che partecipati come membro) --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-folder2 text-primary fs-5"></i>
                        <h2 class="h6 fw-bold mb-0 text-dark">Progetti correlati</h2>
                    </div>
                    <span class="badge rounded-pill bg-light text-secondary border px-2 py-1 small">
                        {{ $user->managedProjects->concat($user->projects)->unique('id')->count() }} progetti
                    </span>
                </div>
                <ul class="list-group list-group-flush border-0">
                    @forelse ($user->managedProjects->concat($user->projects)->unique('id') as $project)
                        <li class="list-group-item d-flex align-items-center gap-3 py-3 px-4 border-bottom position-relative">
                            <div class="flex-grow-1 overflow-hidden">
                                <a class="fw-semibold text-dark text-decoration-none hover-primary d-block text-truncate stretched-link" href="{{ route('projects.show', $project) }}">
                                    {{ $project->name }}
                                </a>
                                <span class="small text-body-secondary">
                                    {{ $project->pm_id === $user->id ? 'Ruolo: Project Manager' : 'Ruolo: Membro del team' }}
                                </span>
                            </div>
                            <x-status :value="$project->status" class="flex-shrink-0" />
                        </li>
                    @empty
                        <li class="list-group-item py-4 px-4 text-center border-0">
                            @include('partials.empty', [
                                'icon' => 'bi-folder2-open',
                                'title' => 'Nessun progetto associato',
                                'text' => 'Questo utente non è ancora stato assegnato ad alcun progetto.'
                            ])
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-page>
