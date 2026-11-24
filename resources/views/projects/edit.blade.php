{{-- Form per la modifica delle impostazioni e dello stato di un progetto esistente --}}
<x-app-layout :title="'Impostazioni · ' . $project->name">
    @include('projects._header', ['tab' => 'edit'])

    <div class="row g-4">
        {{-- Colonna sinistra (8/12): form dati generali (nome, descrizione) con disabilitazione automatica se non modificabile --}}
        <div class="col-lg-8">
            <form method="POST" action="{{ route('projects.update', $project) }}" class="card shadow-sm border-0 overflow-hidden">
                @csrf
                @method('PUT')
                <div class="card-body p-4 d-flex flex-column gap-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h2 class="h6 fw-bold text-dark mb-0">Informazioni generali</h2>
                        @if (! $project->isEditable())
                            <span class="badge bg-secondary-subtle text-secondary border">Sola lettura</span>
                        @endif
                    </div>
                    @if (! $project->isEditable())
                        <fieldset disabled>
                    @endif
                    @include('projects._fields')
                    @if (! $project->isEditable())
                        </fieldset>
                    @endif
                </div>
                <div class="card-footer bg-light py-3 px-4 border-top d-flex justify-content-end gap-2">
                    <a class="btn btn-light border" href="{{ route('projects.show', $project) }}">Annulla</a>
                    @if ($project->isEditable())
                        <x-button class="shadow-sm"><i class="bi bi-check-lg me-1"></i>Salva modifiche</x-button>
                    @endif
                </div>
            </form>
        </div>
        {{-- Colonna destra (4/12): cambio stato del ciclo di vita con conferma Alpine.js per archiviazione --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold text-dark mb-2">Stato del progetto</h2>
                    <p class="small text-body-secondary mb-3">Modifica la fase del ciclo di vita del progetto.</p>
                    {{-- Il modal di conferma (data-confirm) parte solo se si sceglie "archiviato": Alpine toglie l'attributo quando vale null. --}}
                    <form method="POST" action="{{ route('projects.status.update', $project) }}" class="d-flex flex-column gap-2"
                          x-data="{ status: @js($project->status->value) }"
                          :data-confirm="status === @js(\App\Enums\ProjectStatus::Archived->value) ? @js('Archiviare il progetto «' . $project->name . '»? Non comparirà più nell\'elenco dei progetti attivi.') : null"
                          data-confirm-button="Archivia">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="form-select" aria-label="Stato del progetto" x-model="status">
                            @foreach (\App\Enums\ProjectStatus::cases() as $status)
                                @continue($status === \App\Enums\ProjectStatus::Planned && $project->status !== $status)
                                <option value="{{ $status->value }}" @selected($project->status === $status)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error for="status" />
                        <button class="btn btn-primary shadow-sm mt-1">Aggiorna stato</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
