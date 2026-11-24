<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestisce il caricamento della dashboard operativa differenziata in base al ruolo dell'utente.
 */
class DashboardController extends Controller
{
    /**
     * Instrada l'utente alla dashboard specifica in base al suo ruolo primario.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return match (true) {
            $user->hasRole('organizer') => $this->organizer(),
            $user->hasRole('project_manager') => $this->manager($user),
            default => $this->member($user),
        };
    }

    /**
     * Prepara le statistiche e i dati di avanzamento e carico lavoro per l'organizer.
     */
    private function organizer(): View
    {
        $projects = Project::active()
            ->with(['manager', 'tasks'])
            ->orderBy('name')
            ->get();

        $tasks = $projects->flatMap->tasks;

        // Task aperti e in ritardo per member, solo nei progetti attivi: i più carichi in cima.
        $inActiveProject = fn (Builder $q) => $q->whereHas('project', fn (Builder $p) => $p->active());
        $workload = User::role('member')
            ->active()
            ->with('teams')
            ->withCount([
                'tasks as open_tasks_count' => fn (Builder $q) => $inActiveProject($q->where('status', '!=', TaskStatus::Completed)),
                'tasks as overdue_tasks_count' => fn (Builder $q) => $inActiveProject($q->overdue()),
            ])
            ->orderByDesc('open_tasks_count')
            ->orderBy('name')
            ->get();

        return view('dashboard.organizer', [
            'projects' => $projects,
            'workload' => $workload,
            'activeUsers' => User::active()->count(),
            'teams' => Team::count(),
            'openTasks' => $tasks->where('status', '!=', TaskStatus::Completed)->count(),
            'overdue' => $tasks->filter->isOverdue()->count(),
        ]);
    }

    /**
     * Prepara i progetti coordinati, i task in revisione e le metriche per il Project Manager.
     */
    private function manager(User $user): View
    {
        $counts = collect(TaskStatus::cases())
            ->mapWithKeys(fn (TaskStatus $status) => [
                "tasks as {$status->value}_count" => fn (Builder $q) => $q->where('status', $status),
            ])
            ->all();

        $projects = $user->managedProjects()
            ->active()
            ->withCount(['tasks', 'members', ...$counts])
            ->orderBy('name')
            ->get();

        $toReview = Task::whereIn('project_id', $projects->modelKeys())
            ->where('status', TaskStatus::InReview)
            ->with(['project', 'assignees'])
            ->orderBy('end_date')
            ->get();

        return view('dashboard.manager', [
            'projects' => $projects,
            'toReview' => $toReview,
            'openTasks' => $projects->sum('tasks_count') - $projects->sum('completed_count'),
            'overdue' => Task::whereIn('project_id', $projects->modelKeys())->overdue()->count(),
        ]);
    }

    /**
     * Prepara la vista dei task assegnati e dei progetti attivi per il membro del team.
     */
    private function member(User $user): View
    {
        $tasks = $user->tasks()
            ->whereHas('project', fn (Builder $q) => $q->active())
            ->with(['project', 'assignees'])
            ->orderBy('end_date')
            ->get();

        $projects = $user->projects()
            ->active()
            ->with('manager')
            ->withCount('tasks')
            ->orderBy('name')
            ->get();

        return view('dashboard.member', compact('tasks', 'projects'));
    }
}
