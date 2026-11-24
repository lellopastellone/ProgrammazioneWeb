{{-- Vista del calendario personale con i task assegnati e le scadenze mensili dell'utente --}}
<x-page title="Calendario">
    <x-slot name="actions">
        @include('partials.month-nav', ['route' => fn ($m) => route('calendar', ['month' => $m])])
    </x-slot>

    <x-calendar :month="$month" :tasks="$tasks" />
</x-page>
