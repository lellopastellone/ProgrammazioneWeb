{{-- Vista del calendario mensile e delle scadenze filtrato per il singolo progetto --}}
<x-app-layout :title="'Calendario · ' . $project->name">
    @include('projects._header', ['tab' => 'calendar'])

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        @include('partials.month-nav', ['route' => fn ($m) => route('projects.calendar', [$project, 'month' => $m])])
        <div class="d-flex flex-wrap gap-3 small text-body-secondary">
            @foreach (\App\Enums\TaskStatus::cases() as $status)
                <span><i class="bi bi-circle-fill text-{{ $status->color() }} me-1"></i>{{ $status->label() }}</span>
            @endforeach
        </div>
    </div>

    <x-calendar :month="$month" :tasks="$tasks" :iterations="$iterations" />
</x-app-layout>
