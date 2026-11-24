{{-- Campi anagrafici di base per la creazione e modifica del progetto (nome e descrizione) --}}
<div>
    <x-label for="name" value="Nome del progetto" class="fw-medium mb-1 small" />
    <x-input id="name" name="name" :value="old('name', $project?->name)" placeholder="Es. Portale Clienti 2.0" required />
    <x-input-error for="name" class="mt-1" />
</div>
<div>
    <x-label for="description" value="Descrizione e perimetro" class="fw-medium mb-1 small" />
    <textarea id="description" name="description" rows="4" @class(['form-control', 'is-invalid' => $errors->has('description')]) placeholder="Descrivi brevemente gli obiettivi, gli stakeholder e i risultati attesi...">{{ old('description', $project?->description) }}</textarea>
    <x-input-error for="description" class="mt-1" />
</div>

