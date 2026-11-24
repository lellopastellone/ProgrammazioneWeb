<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProjectStatusRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

/**
 * Gestisce il cambio di stato del ciclo di vita di un progetto software.
 */
class ProjectStatusController extends Controller
{
    /**
     * Aggiorna lo stato del progetto applicando le variazioni richieste.
     */
    public function update(UpdateProjectStatusRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return back();
    }
}
