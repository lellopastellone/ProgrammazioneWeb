{{-- Campi del modulo compatto per la creazione e modifica di un'iterazione/sprint (nome, data inizio e fine) --}}
<div class="row g-2 align-items-end">
    <div class="col-md-4">
        <label class="form-label small mb-1">Nome</label>
        <input name="name" class="form-control form-control-sm" value="{{ $iteration?->name }}" placeholder="Es. Sprint 4" required>
    </div>
    {{-- Vincolo min sulla data odierna solo in creazione: in modifica uno sprint già iniziato può avere date nel passato --}}
    <div class="col-6 col-md-3">
        <label class="form-label small mb-1">Inizio</label>
        <input type="date" name="starts_on" class="form-control form-control-sm" value="{{ $iteration?->starts_on->toDateString() }}" @unless ($iteration) min="{{ today()->toDateString() }}" @endunless required>
    </div>
    <div class="col-6 col-md-3">
        <label class="form-label small mb-1">Fine</label>
        <input type="date" name="ends_on" class="form-control form-control-sm" value="{{ $iteration?->ends_on->toDateString() }}" @unless ($iteration) min="{{ today()->toDateString() }}" @endunless required>
    </div>
    <div class="col-md-2 d-grid">
        <button class="btn btn-sm btn-primary">{{ $submit }}</button>
    </div>
</div>
