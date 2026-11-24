<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProjectManagerRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

/**
 * Gestisce l'aggiornamento del Project Manager assegnato a un progetto software.
 */
class ProjectManagerController extends Controller
{
    /**
     * Assegna un nuovo Project Manager al progetto specificato.
     */
    public function update(UpdateProjectManagerRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return back();
    }
}
