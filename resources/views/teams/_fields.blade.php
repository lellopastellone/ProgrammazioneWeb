{{-- Campi del form del team (nome, descrizione) --}}
<div>
    <x-label for="name" value="Nome del team" class="fw-medium mb-1 small" />
    <x-input id="name" name="name" :value="old('name', $team?->name)" placeholder="Es. Backend Core, Mobile App, Design System" required />
    <x-input-error for="name" class="mt-1" />
</div>
<div>
    <x-label for="description" value="Descrizione e scopo del team" class="fw-medium mb-1 small" />
    <textarea id="description" name="description" rows="3" @class(['form-control', 'is-invalid' => $errors->has('description')]) placeholder="Descrivi le aree di responsabilità o le tecnologie usate dal team...">{{ old('description', $team?->description) }}</textarea>
    <x-input-error for="description" class="mt-1" />
</div>

