<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectMemberRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestisce l'elenco, l'aggiunta e la rimozione dei membri operativi nel gruppo di progetto.
 */
class ProjectMemberController extends Controller
{
    /**
     * Mostra la lista dei membri assegnati al progetto e i candidati disponibili.
     */
    public function index(Request $request, Project $project): View
    {
        $members = $project->members()
            ->with('teams')
            ->withCount(['tasks' => fn ($q) => $q->where('project_id', $project->id)])
            ->orderBy('surname')
            ->get();

        // Candidati da aggiungere: member attivi non ancora nel gruppo, filtrati da ?q=.
        // 21 = i 20 mostrati più uno per sapere se ce ne sono altri.
        $candidates = User::role('member')->active()
            ->whereNotIn('id', $members->modelKeys())
            ->search($request->query('q'))
            ->with('teams')
            ->orderBy('surname')
            ->limit(21)
            ->get();

        return view('projects.members', compact('project', 'members', 'candidates'));
    }

    /**
     * Aggiunge un utente al gruppo di lavoro del progetto.
     */
    public function store(StoreProjectMemberRequest $request, Project $project): RedirectResponse
    {
        $project->members()->syncWithoutDetaching([$request->integer('user_id')]);

        return back();
    }

    /**
     * Rimuove l'utente dal gruppo di progetto e revoca le assegnazioni sui relativi task.
     */
    public function destroy(Project $project, User $user): RedirectResponse
    {
        $user->tasks()->detach($project->tasks()->pluck('id'));
        $project->members()->detach($user);

        return back();
    }
}
