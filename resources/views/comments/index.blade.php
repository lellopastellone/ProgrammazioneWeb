{{-- Pagina dedicata alla consultazione e gestione dei commenti associati a un singolo task --}}
<x-page title="Commenti" :subtitle="$task->title">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Progetti</a></li>
        <li class="breadcrumb-item"><a href="{{ route('projects.show', $task->project) }}">{{ $task->project->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">Commenti</li>
    </x-slot>
    <x-slot name="actions">
        <a class="btn btn-light btn-sm border" href="{{ route('tasks.show', $task) }}"><i class="bi bi-arrow-left me-1"></i>Torna al task</a>
    </x-slot>

    <div class="row"><div class="col-lg-8">
        <div class="card">
            @include('comments._list')
            @include('comments._form')
        </div>
    </div></div>
</x-page>
