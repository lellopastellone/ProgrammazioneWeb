{{-- Elenco cronologico dei commenti di un task con autore, data e cancellazione --}}
<ul class="list-group list-group-flush border-0">
    @forelse ($comments as $comment)
        <li class="list-group-item d-flex gap-3 py-3 px-4 border-bottom">
            <x-avatar :user="$comment->author" size="sm" class="align-self-start mt-1" />
            <div class="flex-grow-1 bg-light rounded-3 p-3 border">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div class="d-flex align-items-baseline gap-2">
                        <strong class="text-dark small">{{ $comment->author->full_name }}</strong>
                        <span class="text-body-secondary small" style="font-size: 0.75rem;">
                            {{ $comment->created_at->locale('it')->diffForHumans() }} &bull; {{ $comment->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    @can('delete', $comment)
                        <form method="POST" action="{{ route('comments.destroy', $comment) }}" data-confirm="Eliminare definitivamente questo commento?" data-confirm-button="Elimina">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-link btn-sm p-0 text-danger text-decoration-none" title="Elimina commento">
                                <i class="bi bi-trash small"></i>
                            </button>
                        </form>
                    @endcan
                </div>
                <div class="text-dark small lh-base mb-0" style="white-space: pre-line;">{{ $comment->text }}</div>
            </div>
        </li>
    @empty
        <li class="list-group-item py-4 px-4 text-center border-0">
            @include('partials.empty', [
                'icon' => 'bi-chat-dots',
                'title' => 'Nessun commento',
                'text' => 'Inizia la discussione lasciando una nota per il team.'
            ])
        </li>
    @endforelse
</ul>

