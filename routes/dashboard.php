<?php

/**
 * Rotte della dashboard principale (differenziata per ruolo) e del calendario personale dei task per i membri.
 */

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Una sola dashboard, diversa per ruolo (organizer, project_manager, member).
Route::get('/dashboard', DashboardController::class)->name('dashboard');

// Calendario personale solo per i member (dal pulsante nella loro dashboard); PM e organizer usano quello del progetto.
Route::get('/calendar', CalendarController::class)
    ->role('member')
    ->name('calendar');
