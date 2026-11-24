{{-- Elenco di tutti i team aziendali con conteggio dei membri e avatar dei componenti --}}
<x-page title="Team" subtitle="I gruppi e i reparti operativi dell'organizzazione.">
    {{-- Pulsante per la creazione di un nuovo team (riservato all'Organizer) --}}
    @role('organizer')
        <x-slot name="actions">
            <a class="btn btn-primary shadow-sm" href="{{ route('teams.create') }}">
                <i class="bi bi-plus-lg me-1"></i>Nuovo team
            </a>
        </x-slot>
    @endrole

    {{-- Stato vuoto quando non sono ancora stati configurati team aziendali --}}
    @if ($teams->isEmpty())
        @include('partials.empty', [
            'icon' => 'bi-people',
            'title' => 'Nessun team configurato',
            'text' => auth()->user()->hasRole('organizer') ? 'Crea il primo team per iniziare a raggruppare i collaboratori.' : 'Non è ancora stato configurato alcun team aziendale nell\'organizzazione.',
            'cta' => auth()->user()->hasRole('organizer') ? 'Crea nuovo team' : null,
            'ctaHref' => auth()->user()->hasRole('organizer') ? route('teams.create') : null
        ])
    {{-- Griglia responsive delle schede dei team aziendali --}}
    @else
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
            @foreach ($teams as $team)
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 position-relative" style="transition: transform 0.2s ease, box-shadow 0.2s ease;">
                        <div class="card-body p-4 d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                @hasanyrole('organizer|project_manager')
                                    <a class="h5 fw-bold mb-0 text-dark text-decoration-none hover-primary stretched-link" href="{{ route('teams.show', $team) }}">
                                        {{ $team->name }}
                                    </a>
                                @else
                                    <span class="h5 fw-bold mb-0 text-dark">{{ $team->name }}</span>
                                @endhasanyrole
                                <span class="badge rounded-pill bg-light text-secondary border px-2 py-1 small fw-medium">
                                    {{ $team->users_count }} {{ $team->users_count === 1 ? 'membro' : 'membri' }}
                                </span>
                            </div>
                            
                            <p class="small text-body-secondary mb-0 line-clamp-2" style="min-height: 2.5rem;">
                                {{ $team->description ?: 'Nessuna descrizione per questo team.' }}
                            </p>
                            
                            <div class="d-flex align-items-center justify-content-between mt-auto pt-3 border-top position-relative z-2">
                                <div class="d-flex align-items-center avatar-stack">
                                    @foreach ($team->users->take(5) as $member)
                                        <x-avatar :user="$member" size="sm" />
                                    @endforeach
                                    @if ($team->users->count() > 5)
                                        <span class="badge rounded-pill bg-secondary text-white" style="margin-left: -8px; border: 2px solid #fff; font-size: 0.65rem;">
                                            +{{ $team->users->count() - 5 }}
                                        </span>
                                    @endif
                                </div>
                                @role('organizer')
                                    <a class="btn btn-sm btn-light border text-secondary" href="{{ route('teams.edit', $team) }}" title="Modifica team" aria-label="Modifica {{ $team->name }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endrole
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-page>

