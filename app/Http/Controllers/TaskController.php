<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Enums\TaskStatus;
use App\Http\Requests\TaskRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Gestisce le operazioni CRUD sui task di progetto e la visualizzazione del backlog/kanban.
 */
class TaskController extends Controller
{
    /**
     * Mostra l'elenco dei task del progetto filtrabili per stato, priorità, iterazione e assegnatario.
     */
    public function index(Request $request, Project $project): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
            'priority' => ['nullable', Rule::enum(Priority::class)],
            'assignee' => ['nullable', 'integer'],
            'iteration' => ['nullable', 'integer'],
        ]);

        $tasks = $project->tasks()
            ->with(['assignees', 'iteration'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['priority'] ?? null, fn ($q, $priority) => $q->where('priority', $priority))
            ->when($filters['iteration'] ?? null, fn ($q, $iteration) => $q->where('iteration_id', $iteration))
            ->when($filters['assignee'] ?? null, fn ($q, $userId) => $q->assignedTo((int) $userId))
            ->orderBy('end_date')
            ->get();

        $project->load(['members', 'iterations']);

        return view('tasks.index', compact('project', 'tasks', 'filters'));
    }

    /**
     * Mostra il form per la creazione di un nuovo task nel progetto.
     */
    public function create(Project $project): View
    {
        $project->load('iterations');

        return view('tasks.create', compact('project'));
    }

    /**
     * Valida e crea un nuovo task associandolo al progetto corrente.
     */
    public function store(TaskRequest $request, Project $project): RedirectResponse
    {
        $task = $project->tasks()->create($request->validated());

        return to_route('tasks.show', $task);
    }

    /**
     * Mostra i dettagli del task inclusi membri assegnati, iterazione e commenti.
     */
    public function show(Request $request, Task $task): View
    {
        $task->load(['project', 'iteration', 'assignees', 'comments.author']);

        // Membri del progetto ancora assegnabili, filtrati da ?q=. 21 = i 20 mostrati più uno per sapere se ce ne sono altri.
        // Un member disattivato resta in project_user (per la storia del progetto) ma non è assegnabile.
        $candidates = $task->project->members()
            ->active()
            ->whereNotIn('users.id', $task->assignees->modelKeys())
            ->search($request->query('q'))
            ->with('teams')
            ->orderBy('surname')
            ->limit(21)
            ->get();
        $hasMembers = $task->project->members()->active()->exists();

        return view('tasks.show', compact('task', 'candidates', 'hasMembers'));
    }

    /**
     * Mostra il form per la modifica delle informazioni del task.
     */
    public function edit(Task $task): View
    {
        $task->load('project.iterations');

        return view('tasks.edit', compact('task'));
    }

    /**
     * Aggiorna i dati del task con le modifiche convalidate.
     */
    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->validated());

        return to_route('tasks.show', $task)->with('status', 'Task aggiornato con successo.');
    }

    /**
     * Elimina il task dal database reindirizzando al backlog del progetto.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return to_route('projects.tasks.index', $task->project_id);
    }
}
