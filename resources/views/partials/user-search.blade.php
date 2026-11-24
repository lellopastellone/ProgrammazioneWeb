{{-- Ricerca lato server (?q=) sopra le liste di utenti. Parametri: $candidates (al massimo 21: il 21° segnala che ce ne sono altri), $placeholder (opzionale). --}}
<form method="GET" role="search">
    <div class="input-group input-group-sm">
        <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-search"></i></span>
        <input type="search" name="q" value="{{ is_string(request()->query('q')) ? request()->query('q') : '' }}" class="form-control border-start-0"
               placeholder="{{ $placeholder ?? 'Cerca per nome o cognome' }}" aria-label="{{ $placeholder ?? 'Cerca per nome o cognome' }}">
        <button class="btn btn-light border">Cerca</button>
        @if (filled(request()->query('q')))
            <a href="{{ url()->current() }}" class="btn btn-outline-secondary" title="Azzera ricerca">
                <i class="bi bi-x-lg me-1"></i>Annulla
            </a>
        @endif
    </div>
</form>
