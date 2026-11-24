<?php

/**
 * Rotte per la gestione operativa dei task: consultazione backlog/dettagli, creazione, modifica,
 * avanzamento di stato (workflow), assegnazione membri e inserimento/cancellazione commenti.
 */

use App\Http\Controllers\CommentController;
use App\Http\Controllers\TaskAssigneeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskStatusController;
use Illuminate\Support\Facades\Route;

Route::resource('projects.tasks', TaskController::class)
    ->shallow()
    ->middlewareFor('index', 'can:view,project')
    ->middlewareFor(['create', 'store'], 'can:update,project')
    ->middlewareFor('show', 'can:view,task')
    ->middlewareFor(['edit', 'update', 'destroy'], 'can:manage,task');

// Lo stato si cambia solo da qui, mai dall'update generico del task.
Route::patch('/tasks/{task}/status', [TaskStatusController::class, 'update'])
    ->can('updateStatus', 'task')
    ->name('tasks.status.update');

Route::post('/tasks/{task}/assignees', [TaskAssigneeController::class, 'store'])
    ->can('manage', 'task')
    ->name('tasks.assignees.store');
Route::delete('/tasks/{task}/assignees/{user}', [TaskAssigneeController::class, 'destroy'])
    ->can('manage', 'task')
    ->name('tasks.assignees.destroy');

Route::resource('tasks.comments', CommentController::class)
    ->shallow()
    ->only(['index', 'store', 'destroy'])
    ->middlewareFor('index', 'can:view,task')
    ->middlewareFor('store', 'can:comment,task')
    ->middlewareFor('destroy', 'can:delete,comment');
