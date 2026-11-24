<?php

namespace App\View\Components;

use App\Models\Iteration;
use App\Models\Task;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Componente per la visualizzazione della griglia mensile del calendario.
 * Mostra le scadenze dei task e le fasce temporali delle iterazioni di progetto.
 */
class Calendar extends Component
{
    /**
     * @var list<list<CarbonImmutable>> Settimane lun–dom che coprono il mese.
     * Matrice bidimensionale: con $weeks[$s][$g] si accede al giorno $g della settimana $s.
     */
    public array $weeks;

    /** @var array<string, int> giorno (Y-m-d) => numero di task che scadono quel giorno */
    public array $counts;

    /** @var Collection<int, array{task: Task, from: CarbonImmutable, to: CarbonImmutable}> */
    public Collection $entries;

    /** @var Collection<int, Iteration> */
    public Collection $iterations;

    /**
     * Inizializza il componente calcolando settimane, iterazioni e conteggio scadenze.
     */
    public function __construct(public CarbonInterface $month, iterable $tasks, public bool $compact = false, iterable $iterations = [])
    {
        $this->iterations = collect($iterations)->sortBy('starts_on')->values();
        $this->weeks = self::monthWeeks($month);
        $this->entries = self::entries($tasks);
        $this->counts = collect($this->weeks)->flatten()
            ->mapWithKeys(fn (CarbonImmutable $day) => [$day->toDateString() => $this->dueOn($day)->count()])
            ->all();
    }

    /**
     * Calcola le settimane che coprono il mese, includendo i giorni cuscinetto di inizio e fine.
     */
    public static function monthWeeks(CarbonInterface $month): array
    {
        $month = $month->toImmutable();
        $last = $month->endOfMonth()->endOfWeek(CarbonInterface::SUNDAY);
        $weeks = [];

        for ($start = $month->startOfMonth()->startOfWeek(CarbonInterface::MONDAY); $start <= $last; $start = $start->addWeek()) {
            $weeks[] = array_map(fn (int $i) => $start->addDays($i), range(0, 6));
        }

        return $weeks;
    }

    /**
     * Normalizza e ordina cronologicamente i task dotati di data di inizio o scadenza.
     */
    private static function entries(iterable $tasks): Collection
    {
        return collect($tasks)
            ->map(fn (Task $t) => ['task' => $t, 'from' => ($t->start_date ?? $t->end_date)?->toImmutable(), 'to' => ($t->end_date ?? $t->start_date)?->toImmutable()])
            ->filter(fn (array $e) => $e['from'] !== null)
            ->sortBy(fn (array $e) => $e['from'])
            ->values();
    }

    /**
     * Restituisce la collezione di task che scadono nella data indicata.
     */
    public function dueOn(CarbonInterface $day): Collection
    {
        return $this->entries
            ->filter(fn (array $e) => $e['to']->isSameDay($day))
            ->pluck('task')
            ->values();
    }

    /** @var list<string> Palette di colori tematici per gli sprint */
    public const SPRINT_PALETTE = ['accent', 'info', 'warning', 'success'];

    /**
     * Determina l'eventuale iterazione a cui appartiene il giorno e ne assegna il colore tematico.
     */
    public function sprintOn(CarbonInterface $day): array
    {
        $iteration = $this->iterations->first(fn (Iteration $iteration) => $iteration->includes($day));

        if (! $iteration) {
            return [null, null];
        }

        // Se non è impostato un colore personalizzato, assegna a rotazione ciclica un colore della palette usando il modulo (%).
        $color = $iteration->color ?? self::SPRINT_PALETTE[$this->iterations->search($iteration) % count(self::SPRINT_PALETTE)];

        return [$iteration, $color];
    }

    /**
     * Restituisce la vista Blade per il rendering del calendario.
     */
    public function render(): View
    {
        return view('components.calendar');
    }
}
