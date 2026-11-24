{{-- Form per la creazione di un nuovo progetto con assegnazione del Project Manager responsabile --}}
<x-page title="Nuovo progetto" subtitle="Crea un nuovo spazio di lavoro e assegna il responsabile.">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-decoration-none">Progetti</a></li>
        <li class="breadcrumb-item active text-dark fw-medium" aria-current="page">Nuovo progetto</li>
    </x-slot>

    <div class="row g-4">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('projects.store') }}" class="card shadow-sm border-0 overflow-hidden">
                @csrf
                <div class="card-body p-4 d-flex flex-column gap-3">
                    @include('projects._fields', ['project' => null])
                    <div>
                        <x-label for="pm_id" value="Project Manager" class="fw-medium mb-1 small" />
                        @if ($managers->isNotEmpty())
                            <select id="pm_id" name="pm_id" @class(['form-select', 'is-invalid' => $errors->has('pm_id')])>
                                <option value="">— Seleziona o lascia da assegnare —</option>
                                @foreach ($managers as $manager)
                                    <option value="{{ $manager->id }}" @selected(old('pm_id') == $manager->id)>{{ $manager->full_name }}</option>
                                @endforeach
                            </select>
                        @else
                            <select id="pm_id" name="pm_id" class="form-select text-body-secondary" disabled>
                                <option value="" selected>— Nessun Project Manager disponibile (assegnabile in seguito) —</option>
                            </select>
                        @endif
                        <x-input-error for="pm_id" class="mt-1" />
                    </div>
                </div>
                <div class="card-footer bg-light py-3 px-4 border-top d-flex justify-content-end gap-2">
                    <a class="btn btn-light border" href="{{ route('projects.index') }}">Annulla</a>
                    <x-button class="shadow-sm"><i class="bi bi-check-lg me-1"></i>Crea progetto</x-button>
                </div>
            </form>
        </div>
    </div>
</x-page>
