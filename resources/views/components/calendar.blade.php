{{--
    Componente Calendario:
    - Imposta 'table-layout: fixed' per garantire che le 7 colonne dei giorni abbiano sempre la stessa larghezza.
    - Visualizza i giorni del mese e i relativi task in scadenza tramite indicatori colorati.
--}}
@if ($compact)
    {{-- MODALITÀ COMPATTA (Widget): anteprima mensile semplificata con massimo 3 indicatori per data. --}}
    <table class="table table-sm table-borderless mb-0" style="table-layout: fixed">
        <thead>
            <tr class="small text-uppercase">
                @foreach (['L', 'M', 'M', 'G', 'V', 'S', 'D'] as $weekday)
                    <th class="text-center text-body-secondary fw-semibold">{{ $weekday }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($weeks as $week)
                <tr>
                    @foreach ($week as $day)
                        <td @class(['text-center small', 'opacity-50' => ! $day->isSameMonth($month)])>
                            <span @class(['badge rounded-pill', 'text-bg-primary' => $day->isToday(), 'text-body fw-semibold' => ! $day->isToday()])>{{ $day->day }}</span>
                            @include('partials.calendar-dots', ['dayTasks' => $dueOn($day), 'max' => 3, 'center' => true])
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    @if ($iterations->isNotEmpty())
        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach ($iterations as $iteration)
                @php($color = $iteration->color ?? $sprintOn($iteration->starts_on)[1])
                <span class="badge rounded-pill bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }} border-opacity-25 fw-semibold px-3 py-2">
                    <i class="bi bi-flag-fill me-1"></i>{{ $iteration->name }}
                    <span class="fw-normal ms-1">{{ $iteration->starts_on->format('d/m') }} – {{ $iteration->ends_on->format('d/m') }}</span>
                </span>
            @endforeach
        </div>
    @endif

    {{-- MODALITÀ COMPLETA (Interattiva): cliccando su un giorno si apre il cassetto laterale con i dettagli dei task in scadenza. --}}
    <div x-data="{ selectedDay: null, counts: @js($counts) }" @keydown.escape.window="selectedDay = null"
         class="d-flex flex-column flex-md-row align-items-start">
        <div class="card shadow-sm border-0 overflow-hidden flex-grow-1 w-100">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" style="table-layout: fixed; min-width: 560px">
                    <thead>
                        <tr class="small text-uppercase">
                            @foreach (['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'] as $weekday)
                                <th class="bg-body-secondary text-body-secondary fw-semibold">{{ $weekday }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($weeks as $week)
                            <tr>
                                @foreach ($week as $day)
                                    @php($date = $day->toDateString())
                                    @php([$sprint, $color] = $sprintOn($day))
                                    {{-- Evidenzia i giorni di uno sprint con il proprio colore tematico; sfondo neutro per i giorni fuori mese. --}}
                                    <td @class(['p-0', "bg-{$color} bg-opacity-10" => $sprint, 'bg-body-tertiary' => ! $sprint && ! $day->isSameMonth($month)])>
                                        <button type="button" @class(['btn btn-sm w-100 text-start border-0 rounded-0 p-2', 'text-body-secondary' => ! $day->isSameMonth($month)])
                                                {{-- Evidenzia con sfondo azzurro il giorno attualmente selezionato dall'utente --}}
                                                :class="selectedDay === '{{ $date }}' ? 'bg-primary-subtle' : ''"
                                                {{-- Accessibilità web (ARIA): fornisce indicazioni vocali agli screen reader per non vedenti --}}
                                                :aria-pressed="selectedDay === '{{ $date }}'"
                                                aria-label="{{ $day->locale('it')->translatedFormat('j F') }}: {{ $counts[$date] }} task in scadenza"
                                                @click="selectedDay = selectedDay === '{{ $date }}' ? null : '{{ $date }}'">
                                            <span @class(['badge rounded-pill', 'text-bg-primary' => $day->isToday(), 'text-body fw-semibold' => ! $day->isToday()])>{{ $day->day }}</span>
                                            @include('partials.calendar-dots', ['dayTasks' => $dueOn($day), 'max' => 5, 'center' => false])
                                        </button>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Cassetto laterale a comparsa: gestito con transizioni animate (allargamento della colonna e dissolvenza della card). --}}
        <aside class="col-12 col-md-5 col-xl-4" x-show="selectedDay !== null" style="display: none" aria-live="polite"
               x-transition:enter="transition-panel-in overflow-hidden"
               x-transition:enter-start="w-0"
               x-transition:leave="transition-panel-out overflow-hidden"
               x-transition:leave-end="w-0">
            <div class="card shadow-sm border-0 overflow-hidden mt-3 mt-md-0 ms-md-3" x-show="selectedDay !== null" style="display: none"
                 x-transition:enter.opacity.duration.200ms.delay.300ms
                 x-transition:leave.opacity.duration.150ms>
                <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between gap-2 py-3">
                    <h2 class="h6 fw-semibold mb-0 text-capitalize"
                        x-text="selectedDay && new Date(selectedDay + 'T00:00').toLocaleDateString('it-IT', { weekday: 'long', day: 'numeric', month: 'long' })"></h2>
                    <button type="button" class="btn-close" aria-label="Chiudi" @click="selectedDay = null"></button>
                </div>
                <ul class="list-group list-group-flush overflow-auto mh-card">
                    {{-- Elenco delle scadenze: mostra solo i task la cui data di fine corrisponde al giorno selezionato. --}}
                    @foreach ($entries as $entry)
                        @php($task = $entry['task'])
                        <li class="list-group-item py-3 position-relative" x-show="selectedDay === '{{ $entry['to']->toDateString() }}'" x-transition:enter.opacity.duration.300ms style="display: none">
                            <a class="fw-medium text-decoration-none stretched-link" href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                <x-status :value="$task->status" />
                                <x-status :value="$task->priority" :pill="false" />
                            </div>
                            <div class="small text-body-secondary mt-1">
                                @if ($task->relationLoaded('project')){{ $task->project->name }} · @endif{{ $entry['from']->format('d/m') }} → {{ $entry['to']->format('d/m') }}
                            </div>
                        </li>
                    @endforeach
                    <li class="list-group-item py-3 small text-body-secondary" x-show="selectedDay !== null && ! counts[selectedDay]" x-transition:enter.opacity.duration.300ms style="display: none">Nessun task in scadenza in questa data.</li>
                </ul>
            </div>
        </aside>
    </div>
@endif
