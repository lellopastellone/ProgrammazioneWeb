<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalendarRequest;
use App\Models\Iteration;
use App\Models\Project;
use App\View\Components\Calendar;
use Illuminate\View\View;

/**
 * Gestisce la visualizzazione del calendario di un singolo progetto con relativi task e iterazioni.
 */
class ProjectCalendarController extends Controller
{
    /**
     * Mostra il calendario del progetto evidenziando task e sprint per il mese selezionato.
     */
    public function __invoke(CalendarRequest $request, Project $project): View
    {
        $month = $request->month();

        $tasks = $project->tasks()
            ->inMonth($month)
            ->with('assignees')
            ->orderBy('start_date')
            ->get();

        $allIterations = $project->iterations;
        $allIterations->each(function (Iteration $iteration, int $index) {
            $iteration->color = Calendar::SPRINT_PALETTE[$index % count(Calendar::SPRINT_PALETTE)];
        });

        $iterations = $allIterations->filter(fn (Iteration $iteration) =>
            $iteration->starts_on <= $month->endOfMonth() && $iteration->ends_on >= $month->startOfMonth()
        )->values();

        return view('projects.calendar', compact('project', 'month', 'tasks', 'iterations'));
    }
}
