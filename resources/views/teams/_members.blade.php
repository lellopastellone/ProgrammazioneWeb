{{-- Elenco dei membri appartenenti al team con opzione di rimozione --}}
<div class="card shadow-sm border-0 overflow-hidden">
    <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-people text-primary fs-5"></i>
            <h2 class="h6 fw-bold mb-0 text-dark">Membri del team</h2>
        </div>
        <span class="badge rounded-pill bg-light text-secondary border px-2 py-1 small">
            @php
                $membersList = $members ?? $team->users;
                $totalCount = isset($members) ? $members->total() : ($team->users_count ?? $membersList->count());
            @endphp
            {{ $totalCount }} {{ $totalCount === 1 ? 'componente' : 'componenti' }}
        </span>
    </div>
    <ul class="list-group list-group-flush border-0">
        @forelse ($membersList as $member)
            <li class="list-group-item d-flex align-items-center gap-3 py-3 px-4 border-bottom position-relative">
                <x-avatar :user="$member" size="sm" />
                <div class="flex-grow-1 overflow-hidden">
                    @role('organizer')
                        <a class="fw-semibold text-dark text-decoration-none hover-primary text-truncate d-block stretched-link" href="{{ route('users.show', $member) }}">
                            {{ $member->full_name }}
                        </a>
                    @else
                        <span class="fw-semibold text-dark text-truncate d-block">
                            {{ $member->full_name }}
                        </span>
                    @endrole
                    @if ($member->email)
                        <div class="small text-body-secondary text-truncate">{{ $member->email }}</div>
                    @endif
                </div>
                @unless ($member->active)
                    <span class="badge rounded-pill bg-light text-secondary border fw-medium px-2 py-1">Disattivato</span>
                @endunless
                @if ($manage ?? false)
                    <form method="POST" action="{{ route('teams.members.destroy', [$team, $member]) }}" class="position-relative z-1" data-confirm="Rimuovere {{ $member->full_name }} dal team «{{ $team->name }}»?" data-confirm-button="Rimuovi">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-light border text-danger" title="Rimuovi dal team">
                            <i class="bi bi-person-dash me-1"></i>Rimuovi
                        </button>
                    </form>
                @endif
            </li>
        @empty
            <li class="list-group-item py-4 px-4 text-center border-0">
                @include('partials.empty', [
                    'icon' => 'bi-people',
                    'title' => 'Nessun membro',
                    'text' => 'Questo team non ha ancora nessun membro assegnato.'
                ])
            </li>
        @endforelse
    </ul>
    @if (isset($members) && $members->hasPages())
        <div class="card-footer bg-transparent py-3 border-top">
            {{ $members->links() }}
        </div>
    @endif
</div>

