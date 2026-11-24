<?php

namespace App\Http\Controllers;

use App\Http\Requests\IterationRequest;
use App\Models\Iteration;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

/**
 * Gestisce la creazione, l'aggiornamento e l'eliminazione delle iterazioni (sprint) di progetto.
 */
class IterationController extends Controller
{
    /**
     * Salva una nuova iterazione nel progetto specificato.
     */
    public function store(IterationRequest $request, Project $project): RedirectResponse
    {
        $project->iterations()->create($request->validated());

        return to_route('projects.show', $project);
    }

    /**
     * Aggiorna le date o il nome dell'iterazione specificata.
     */
    public function update(IterationRequest $request, Iteration $iteration): RedirectResponse
    {
        $iteration->update($request->validated());

        return to_route('projects.show', $iteration->project_id)->with('status', 'Iterazione aggiornata con successo.');
    }

    /**
     * Elimina l'iterazione dissociandone i task collegati.
     */
    public function destroy(Iteration $iteration): RedirectResponse
    {
        $iteration->delete();

        return to_route('projects.show', $iteration->project_id);
    }
}
