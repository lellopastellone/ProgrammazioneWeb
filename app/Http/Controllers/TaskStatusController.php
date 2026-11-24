<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;

/**
 * Gestisce l'avanzamento e la transizione di stato dei task nel flusso di lavoro.
 */
class TaskStatusController extends Controller
{
    /**
     * Aggiorna lo stato del task registrando l'eventuale timestamp di completamento.
     * Il primo task che parte avvia anche il progetto ancora pianificato, se ha un PM attivo:
     * senza PM il progetto resta pianificato (l'organizer può solo archiviarlo o eliminarlo).
     */
    public function update(UpdateTaskStatusRequest $request, Task $task): RedirectResponse
    {
        $task->status = $request->enum('status', TaskStatus::class);
        $task->completed_at = $task->status === TaskStatus::Completed ? now() : null;
        $task->save();

        $project = $task->project;

        if ($task->status === TaskStatus::InProgress && $project->status === ProjectStatus::Planned && $project->hasActiveManager()) {
            $project->update(['status' => ProjectStatus::InProgress]);
        }

        return back();
    }
}
