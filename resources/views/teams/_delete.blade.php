{{-- Sezione per l'eliminazione del team con verifica della presenza di membri associati --}}
@php $count = $team->users_count; @endphp
<div class="card shadow-sm border-0 border-top border-danger border-3">
    <div class="card-body p-4">
        <h2 class="h6 fw-bold text-danger mb-2">Elimina team</h2>
        @if ($count)
            <p class="small text-body-secondary mb-3">Questo team ha ancora <strong class="text-dark">{{ $count }} {{ $count === 1 ? 'membro' : 'membri' }}</strong>. Per poterlo eliminare devi prima rimuovere tutti i componenti.</p>
        @else
            <p class="small text-body-secondary mb-3">Elimina definitivamente questo team dall'organizzazione. L'operazione non può essere annullata.</p>
        @endif
        <form method="POST" action="{{ route('teams.destroy', $team) }}" data-confirm="Eliminare definitivamente il team «{{ $team->name }}»?" data-confirm-button="Elimina">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger shadow-sm" @disabled($count)>
                <i class="bi bi-trash me-1"></i>Elimina team
            </button>
        </form>
    </div>
</div>

