{{-- Componente riutilizzabile per la ricerca server-side e l'assegnazione rapida di collaboratori (progetti, team, task) --}}
@props([
    'action',
    'candidates',
    'buttonLabel' => 'Aggiungi',
    'showTeams' => false,
    'maxHeight' => '280px',
    'placeholder' => 'Cerca per nome o cognome',
    'emptyText' => 'Tutti i collaboratori disponibili sono già stati associati.',
])

{{-- Solo un termine stringa conta come ricerca: con ?q[]=x stampare l'array darebbe un 500. --}}
@php($q = is_string(request()->query('q')) ? request()->query('q') : '')

<div class="d-flex flex-column gap-2">
    @include('partials.user-search', ['placeholder' => $placeholder])

    @if ($candidates->isNotEmpty())
        @if (filled($q))
            <div class="d-flex justify-content-between align-items-center small text-body-secondary">
                <span>Risultati per «<strong>{{ $q }}</strong>»:</span>
                <span class="badge bg-light text-secondary border">{{ $candidates->count() > 20 ? '20+' : $candidates->count() }}</span>
            </div>
        @endif

        <ul class="list-group list-group-flush border rounded-3 overflow-y-auto" style="max-height: {{ $maxHeight }};">
            @foreach ($candidates->take(20) as $candidate)
                <li class="list-group-item d-flex align-items-center justify-content-between gap-2 py-2 px-3">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <x-avatar :user="$candidate" size="sm" />
                        <div class="overflow-hidden">
                            <div class="fw-semibold text-dark text-truncate small">{{ $candidate->full_name }}</div>
                            @if ($showTeams && $candidate->relationLoaded('teams') && $candidate->teams->isNotEmpty())
                                <div class="text-body-secondary text-truncate" style="font-size: 0.75rem;">
                                    {{ $candidate->teams->pluck('name')->join(', ') }}
                                </div>
                            @elseif ($showTeams && $candidate->relationLoaded('teams'))
                                <div class="text-body-secondary text-truncate" style="font-size: 0.75rem;">
                                    Nessun team
                                </div>
                            @endif
                        </div>
                    </div>
                    <form method="POST" action="{{ $action }}" class="m-0 flex-shrink-0">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $candidate->id }}">
                        <button class="btn btn-sm btn-outline-primary text-nowrap" title="{{ $buttonLabel }} {{ $candidate->full_name }}">
                            <i class="bi bi-plus-lg me-1"></i>{{ $buttonLabel }}
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>

        @if ($candidates->count() > 20)
            <div class="alert alert-light border small text-body-secondary py-2 px-3 mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-info-circle text-primary flex-shrink-0"></i>
                <span>{{ filled($q) ? 'Mostrati i primi 20: affina la ricerca per trovare altri collaboratori.' : 'Mostrati i primi 20: usa la barra di ricerca per trovare altri collaboratori.' }}</span>
            </div>
        @endif
    @else
        <div class="alert alert-light border small text-body-secondary mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-info-circle text-primary flex-shrink-0"></i>
            <span>{{ filled($q) ? 'Nessun collaboratore trovato.' : $emptyText }}</span>
        </div>
    @endif
</div>

