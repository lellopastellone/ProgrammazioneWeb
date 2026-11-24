{{-- Campi del form utente (anagrafica, email con dominio aziendale, ruolo Spatie e team per i member) --}}
{{-- Dati anagrafici di base (Nome, Cognome) e indirizzo email aziendale --}}
<div class="row g-3">
    <div class="col-sm-6">
        <x-label for="name" value="Nome" class="fw-medium mb-1 small" />
        <x-input id="name" name="name" :value="old('name', $user?->name)" placeholder="Nome" required />
        <x-input-error for="name" class="mt-1" />
    </div>
    <div class="col-sm-6">
        <x-label for="surname" value="Cognome" class="fw-medium mb-1 small" />
        <x-input id="surname" name="surname" :value="old('surname', $user?->surname)" placeholder="Cognome" required />
        <x-input-error for="surname" class="mt-1" />
    </div>
    <div class="col-12">
        <x-label for="email" value="Indirizzo email aziendale" class="fw-medium mb-1 small" />
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-secondary"><i class="bi bi-envelope"></i></span>
            <x-input id="email" type="email" name="email" :value="old('email', $user?->email)" required :placeholder="$emailDomain ? 'nome.cognome@' . $emailDomain : 'nome@azienda.it'" class="border-start-0" />
        </div>
        <x-input-error for="email" class="mt-1" />
    </div>
</div>

{{-- Nei team stanno solo i member: la scelta dei team compare (e viene inviata) solo col ruolo member. --}}
<div class="d-flex flex-column gap-4" x-data="{ role: @js(old('role', $user?->getRoleNames()->first())) }">
{{-- Selezione del ruolo organizzativo Spatie (Organizer, Project Manager, Member) --}}
<fieldset>
    <legend class="fs-6 fw-bold text-dark mb-2">Ruolo organizzativo</legend>
    @if ($user)
        <div class="d-flex flex-wrap align-items-center gap-2">
            @include('partials.role-badges')
        </div>
    @else
        <div class="d-flex flex-wrap gap-3">
            @foreach (['organizer' => ['Organizer', 'bi-shield-check'], 'project_manager' => ['Project Manager', 'bi-briefcase'], 'member' => ['Member', 'bi-person']] as $role => [$roleLabel, $roleIcon])
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="role" value="{{ $role }}" id="role-{{ $role }}" x-model="role" @checked(old('role') === $role) required>
                    <label class="form-check-label fw-medium text-dark" for="role-{{ $role }}">
                        <i class="bi {{ $roleIcon }} me-1 text-secondary"></i>{{ $roleLabel }}
                    </label>
                </div>
            @endforeach
        </div>
        <x-input-error for="role" class="mt-1" />
    @endif
</fieldset>

{{-- Team aziendali di appartenenza (sezione condizionale visibile solo per il ruolo Member) --}}
<fieldset x-show="role === 'member'" :disabled="role !== 'member'" style="display: none">
    <legend class="fs-6 fw-bold text-dark mb-2">Team di appartenenza</legend>
    <p class="small text-body-secondary mb-2">Seleziona uno o più team per questo membro:</p>
    <div class="d-flex flex-wrap gap-2">
        @forelse ($teams as $team)
            <input type="checkbox" class="btn-check" name="teams[]" value="{{ $team->id }}" id="team-{{ $team->id }}" autocomplete="off" @checked(in_array($team->id, old('teams', $user?->teams->modelKeys() ?? [])))>
            <label class="btn btn-sm btn-outline-primary" for="team-{{ $team->id }}">
                <i class="bi bi-people me-1"></i>{{ $team->name }}
            </label>
        @empty
            <span class="small text-body-secondary">Nessun team disponibile.</span>
        @endforelse
    </div>
    <x-input-error for="teams" class="mt-1" />
</fieldset>
</div>

