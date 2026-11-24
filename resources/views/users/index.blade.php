{{-- Tabella di consultazione e gestione di tutti gli utenti registrati con ordinamento per nominativo, ruolo, team e stato --}}
@php $isOrganizer = auth()->user()->hasRole('organizer'); @endphp

<x-page title="Utenti" subtitle="Tutti gli account registrati nell'organizzazione e i relativi ruoli.">
    {{-- Pulsante per la registrazione di un nuovo utente (riservato all'Organizer) --}}
    @if ($isOrganizer)
        <x-slot name="actions">
            <a class="btn btn-primary shadow-sm" href="{{ route('users.create') }}">
                <i class="bi bi-person-plus me-1"></i>Nuovo utente
            </a>
        </x-slot>
    @endif

    {{-- Tabella responsive con ordinamento interattivo delle colonne per nome, ruolo, team e stato --}}
    <div class="card shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-people text-primary fs-5"></i>
                <h2 class="h6 fw-bold mb-0 text-dark">Elenco utenti</h2>
            </div>
            <span class="badge rounded-pill bg-light text-secondary border px-2 py-1 small">
                {{ $users->total() }} utenti
            </span>
        </div>
        {{-- Ricerca lato server (?q=), 25 utenti per pagina: l'ordinamento delle colonne vale per la pagina visibile. --}}
        <div class="card-body border-bottom py-3">
            @include('partials.user-search', ['placeholder' => $isOrganizer ? 'Cerca per nome, cognome o email' : null])
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="small text-uppercase bg-light">
                    <tr>
                        <x-sort-th label="Utente" default class="ps-4 py-3 text-secondary fw-semibold" />
                        <x-sort-th label="Ruolo" class="py-3 text-secondary fw-semibold" />
                        <x-sort-th label="Team" class="py-3 text-secondary fw-semibold" />
                        <x-sort-th label="Stato account" class="pe-4 py-3 text-secondary fw-semibold" />
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr @class(['position-relative', 'cursor-pointer' => $isOrganizer])>
                            <td class="ps-4 py-3" data-sort-value="{{ $user->surname }} {{ $user->name }}">
                                <div class="d-flex align-items-center gap-3">
                                    <x-avatar :user="$user" />
                                    <div>
                                        @if ($isOrganizer)
                                            <a class="fw-semibold text-dark text-decoration-none hover-primary stretched-link d-block" href="{{ route('users.show', $user) }}">
                                                {{ $user->full_name }}
                                            </a>
                                            <div class="small text-body-secondary">{{ $user->email }}</div>
                                        @else
                                            <span class="fw-semibold text-dark d-block">{{ $user->full_name }}</span>
                                            <div class="small text-body-secondary">{{ $user->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3"><div class="d-flex flex-wrap gap-1">@include('partials.role-badges')</div></td>
                            <td class="py-3 small" data-sort-value="{{ $user->teams->pluck('name')->sort()->join(', ') }}">
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse ($user->teams as $team)
                                        <span class="badge rounded-pill bg-light text-secondary border fw-medium">{{ $team->name }}</span>
                                    @empty
                                        <span class="text-body-secondary">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="pe-4 py-3" data-sort-value="{{ $user->active ? 'Attivo' : 'Disattivato' }}">
                                @if ($user->active)
                                    <span class="badge rounded-pill text-bg-success fw-medium px-2 py-1">
                                        <i class="bi bi-check-circle me-1"></i>Attivo
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-light text-secondary border fw-medium px-2 py-1">
                                        <i class="bi bi-dash-circle me-1"></i>Disattivato
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 small text-body-secondary">Nessun utente trovato.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="card-footer bg-transparent py-3 border-top">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</x-page>

