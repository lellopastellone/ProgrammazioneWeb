{{--
    Puntini dei task di un giorno (colore dello stato). Parametri: $dayTasks, $max, $center.
    lh-1 e un puntino invisibile nei giorni vuoti: tutte le righe restano alte uguali.
--}}
<div @class(['d-flex align-items-center gap-1 lh-1 mt-1 small text-nowrap overflow-hidden', 'justify-content-center' => $center])>
    @forelse ($dayTasks->take($max) as $task)
        <span class="d-inline-block rounded-circle p-1 bg-{{ $task->status->color() }}"></span>
    @empty
        <span class="d-inline-block rounded-circle p-1"></span>
    @endforelse
    @if (! $center && $dayTasks->count() > $max)
        <span class="text-body-secondary">+{{ $dayTasks->count() - $max }}</span>
    @endif
</div>
