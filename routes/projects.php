<?php

/**
 * Rotte per la gestione dei progetti: visualizzazione elenco, creazione, modifica dettagli,
 * aggiornamento dello stato operativo, riassegnazione Project Manager, gestione membri del team,
 * calendario integrato e gestione delle iterazioni/sprint.
 */

use App\Http\Controllers\IterationController;
use App\Http\Controllers\ProjectCalendarController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectManagerController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\ProjectStatusController;
use Illuminate\Support\Facades\Route;

// I progetti si archiviano con PATCH /projects/{project}/status; si eliminano solo quelli che non hanno mai avuto un PM (ProjectPolicy::delete).
Route::resource('projects', ProjectController::class)
    ->middlewareFor(['create', 'store'], 'permission:projects.create')
    ->middlewareFor('show', 'can:view,project')
    ->middlewareFor('edit', 'can:manage,project')
    ->middlewareFor('update', 'can:update,project')
    ->middlewareFor('destroy', 'can:delete,project');

Route::prefix('/projects/{project}')->name('projects.')->group(function () {
    Route::patch('/status', [ProjectStatusController::class, 'update'])
        ->can('changeStatus', 'project')
        ->name('status.update');

    Route::patch('/manager', [ProjectManagerController::class, 'update'])
        ->can('assignManager', 'project')
        ->name('manager.update');

    Route::get('/members', [ProjectMemberController::class, 'index'])
        ->can('view', 'project')
        ->name('members.index');
    Route::post('/members', [ProjectMemberController::class, 'store'])
        ->can('update', 'project')
        ->name('members.store');
    Route::delete('/members/{user}', [ProjectMemberController::class, 'destroy'])
        ->can('update', 'project')
        ->name('members.destroy');

    Route::get('/calendar', ProjectCalendarController::class)
        ->can('view', 'project')
        ->name('calendar');
});

Route::resource('projects.iterations', IterationController::class)
    ->shallow()
    ->only(['store', 'update', 'destroy'])
    ->middlewareFor('store', 'can:update,project')
    ->middlewareFor(['update', 'destroy'], 'can:manage,iteration');
