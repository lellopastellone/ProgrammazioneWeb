<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskAssigneeRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

/**
 * Gestisce l'assegnazione e la rimozione dei membri operativi sui singoli task.
 */
class TaskAssigneeController extends Controller
{
    /**
     * Assegna un utente al task registrando la data di assegnazione.
     */
    public function store(StoreTaskAssigneeRequest $request, Task $task): RedirectResponse
    {
        $task->assignees()->syncWithoutDetaching([
            $request->integer('user_id') => ['assigned_at' => now()],
        ]);

        return back();
    }

    /**
     * Rimuove l'utente dall'elenco degli assegnatari del task.
     */
    public function destroy(Task $task, User $user): RedirectResponse
    {
        $task->assignees()->detach($user);

        return back();
    }
}
