{{-- Campi del form di creazione e modifica del task (titolo, descrizione, iterazione, priorità, date) --}}
<div>
    <x-label for="title" value="Titolo del task" class="fw-medium mb-1 small" />
    <x-input id="title" name="title" :value="old('title', $task?->title)" placeholder="Es. Implementazione endpoint autenticazione" required />
    <x-input-error for="title" class="mt-1" />
</div>
<div>
    <x-label for="description" value="Descrizione e criteri di accettazione" class="fw-medium mb-1 small" />
    <textarea id="description" name="description" rows="4" @class(['form-control', 'is-invalid' => $errors->has('description')]) placeholder="Dettaglia i requisiti, i casi limite e come validare il completamento...">{{ old('description', $task?->description) }}</textarea>
    <x-input-error for="description" class="mt-1" />
</div>
<div class="row g-3">
    <div class="col-md-6">
        <x-label for="iteration_id" value="Iterazione / Sprint" class="fw-medium mb-1 small" />
        <select id="iteration_id" name="iteration_id" @class(['form-select', 'is-invalid' => $errors->has('iteration_id')])>
            <option value="">— Nessuna (resta nel backlog generale) —</option>
            @foreach ($iterations as $iteration)
                <option value="{{ $iteration->id }}" @selected(old('iteration_id', $task?->iteration_id) == $iteration->id)>
                    {{ $iteration->name }} ({{ $iteration->starts_on->format('d/m') }} – {{ $iteration->ends_on->format('d/m') }})
                </option>
            @endforeach
        </select>
        <x-input-error for="iteration_id" class="mt-1" />
    </div>
    <div class="col-md-6">
        <x-label for="priority" value="Livello di priorità" class="fw-medium mb-1 small" />
        <select id="priority" name="priority" @class(['form-select', 'is-invalid' => $errors->has('priority')])>
            @foreach (\App\Enums\Priority::cases() as $priority)
                <option value="{{ $priority->value }}" @selected(old('priority', $task?->priority?->value ?? 'medium') === $priority->value)>
                    {{ $priority->label() }}
                </option>
            @endforeach
        </select>
        <x-input-error for="priority" class="mt-1" />
    </div>
    <div class="col-6">
        <x-label for="start_date" value="Data di inizio" class="fw-medium mb-1 small" />
        <x-input type="date" id="start_date" name="start_date" :value="old('start_date', $task?->start_date?->toDateString())" required />
        <x-input-error for="start_date" class="mt-1" />
    </div>
    <div class="col-6">
        <x-label for="end_date" value="Data di scadenza" class="fw-medium mb-1 small" />
        <x-input type="date" id="end_date" name="end_date" :value="old('end_date', $task?->end_date?->toDateString())" required />
        <x-input-error for="end_date" class="mt-1" />
    </div>
</div>

