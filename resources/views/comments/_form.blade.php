{{-- Form per l'inserimento di un nuovo commento associato al task --}}
@can('comment', $task)
    <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="card-footer bg-light p-3 border-top">
        @csrf
        <div class="mb-2">
            <textarea name="text" rows="2" @class(['form-control bg-white shadow-none', 'is-invalid' => $errors->has('text')]) placeholder="Scrivi un aggiornamento o una nota per il gruppo..." aria-label="Commento" required>{{ old('text') }}</textarea>
            <x-input-error for="text" class="mt-1" />
        </div>
        <div class="d-flex justify-content-end">
            <button class="btn btn-sm btn-primary shadow-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-send-fill"></i>
                <span>Pubblica commento</span>
            </button>
        </div>
    </form>
@endcan

