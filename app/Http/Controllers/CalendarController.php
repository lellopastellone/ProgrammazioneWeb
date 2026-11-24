<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalendarRequest;
use Illuminate\View\View;

/**
 * Gestisce la visualizzazione del calendario personale con i task assegnati all'utente.
 */
class CalendarController extends Controller
{
    /**
     * Mostra la vista del calendario personale per il mese specificato.
     */
    public function __invoke(CalendarRequest $request): View
    {
        $month = $request->month();

        $tasks = $request->user()->tasks()
            ->whereHas('project', fn ($q) => $q->active())
            ->inMonth($month)
            ->with(['project', 'assignees'])
            ->orderBy('start_date')
            ->get();

        return view('calendar', compact('month', 'tasks'));
    }
}
