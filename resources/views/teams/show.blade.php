{{-- Scheda di dettaglio del team con l'elenco dei membri che ne fanno parte --}}
<x-page :title="$team->name" :subtitle="$team->description ?: 'Nessuna descrizione per questo team.'">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('teams.index') }}" class="text-decoration-none">Team</a></li>
        <li class="breadcrumb-item active text-dark fw-medium" aria-current="page">{{ $team->name }}</li>
    </x-slot>
    @role('organizer')
        <x-slot name="actions">
            <a class="btn btn-light border shadow-sm" href="{{ route('teams.edit', $team) }}">
                <i class="bi bi-pencil me-1"></i>Modifica team
            </a>
        </x-slot>
    @endrole

    @include('teams._members')
</x-page>

