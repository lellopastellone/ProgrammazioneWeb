<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestisce il ciclo di vita dei progetti: visualizzazione, creazione, modifica e consultazione dettagli.
 */
class ProjectController extends Controller
{
    /**
     * Mostra l'elenco dei progetti visibili all'utente con conteggi dei task.
     */
    public function index(Request $request): View
    {
        $projects = Project::visibleTo($request->user())
            ->with(['manager', 'members'])
            ->withCount(['tasks', 'tasks as completed_tasks_count' => fn ($q) => $q->where('status', TaskStatus::Completed)])
            ->latest()
            ->get();

        return view('projects.index', compact('projects'));
    }

    /**
     * Mostra il form per la creazione di un nuovo progetto con i manager disponibili.
     */
    public function create(): View
    {
        $managers = User::role('project_manager')->active()->orderBy('surname')->get();

        return view('projects.create', compact('managers'));
    }

    /**
     * Salva un nuovo progetto impostando l'utente autenticato come creatore.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = new Project($request->validated());
        $project->created_by = $request->user()->id;
        $project->save();

        return to_route('projects.show', $project);
    }

    /**
     * Mostra la pagina riassuntiva del progetto con task, membri e iterazioni.
     */
    public function show(Request $request, Project $project): View
    {
        $project->load(['creator', 'manager', 'members.teams', 'iterations'])
            ->loadCount('tasks');

        $tasks = $project->tasks()
            ->with('assignees')
            ->orderBy('end_date')
            ->get();

        $managers = $request->user()->can('assignManager', $project)
            ? User::role('project_manager')->active()->orderBy('surname')->get()
            : collect();

        return view('projects.show', compact('project', 'tasks', 'managers'));
    }

    /**
     * Mostra il form per la modifica delle informazioni di base del progetto.
     */
    public function edit(Project $project): View
    {
        return view('projects.edit', compact('project'));
    }

    /**
     * Aggiorna nome e descrizione del progetto con i dati validati.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return to_route('projects.show', $project)->with('status', 'Progetto aggiornato con successo.');
    }

    /**
     * Elimina un progetto senza PM (vuoto: task, membri e iterazioni li aggiunge solo il PM).
     */
    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return to_route('projects.index')->with('status', 'Progetto eliminato.');
    }
}
