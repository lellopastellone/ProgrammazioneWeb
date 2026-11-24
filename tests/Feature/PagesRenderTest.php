<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\UserSeeder;

it('keeps the demo tasks inside their sprint', function () {
    $this->seed([UserSeeder::class, ProjectSeeder::class]);

    $outside = Task::with('iteration')->has('iteration')->get()
        ->filter(fn (Task $task) => $task->start_date->lt($task->iteration->starts_on) || $task->end_date->gt($task->iteration->ends_on));

    expect($outside->pluck('title')->all())->toBe([]);
});

// Smoke test delle view: con i dati demo ogni pagina risponde 200 (o 403 se il ruolo non può), mai 500.
it('renders every page for every demo role', function (string $email) {
    $this->seed([UserSeeder::class, ProjectSeeder::class]);

    $user = User::where('email', $email)->firstOrFail();
    $project = Project::where('pm_id', User::where('email', 'pm@example.com')->value('id'))->firstOrFail();
    $task = Task::where('project_id', $project->id)->has('comments')->first() ?? $project->tasks()->firstOrFail();
    $team = Team::firstOrFail();
    $other = User::where('email', 'member@example.com')->firstOrFail();

    $pages = [
        '/', '/dashboard', '/calendar', '/user/profile',
        '/projects', '/projects?status=archived', '/projects/create',
        "/projects/{$project->id}", "/projects/{$project->id}/edit", "/projects/{$project->id}/members", "/projects/{$project->id}/calendar",
        "/projects/{$project->id}/tasks", "/projects/{$project->id}/tasks?view=kanban", "/projects/{$project->id}/tasks/create",
        "/tasks/{$task->id}", "/tasks/{$task->id}/edit", "/tasks/{$task->id}/comments",
        '/teams', '/teams/create', "/teams/{$team->id}", "/teams/{$team->id}/edit",
        '/users', '/users/create', "/users/{$other->id}", "/users/{$other->id}/edit", '/users?q=member',
        '/settings/mail', '/user/verify-two-factor',
        "/projects/{$project->id}/members?q=a", "/teams/{$team->id}/edit?q=a", "/tasks/{$task->id}?q=a",
    ];

    // Modifica utente e server SMTP chiedono la password (password.confirm): qui è già confermata.
    $this->withSession(confirmedPassword());

    foreach ($pages as $page) {
        $status = $this->actingAs($user)->get($page)->status();
        expect($status)->toBeIn([200, 403], "{$email} {$page} → {$status}");
    }
})->with(['organizer@example.com', 'pm@example.com', 'member@example.com']);

it('renders the guest pages', function (string $page) {
    auth()->logout();

    $this->get($page)->assertOk();
})->with(['/', '/login', '/forgot-password', '/reset-password/token?email=a@example.com', '/set-password/token?email=a@example.com']);

it('answers 404 to non numeric ids instead of querying the database', function (string $page) {
    $this->actingAs(userWithRole('organizer'))->get($page)->assertNotFound();
})->with(['/projects/abc', '/tasks/1x', '/users/99999999999999999999', '/teams/abc/edit']);

it('reads the calendar month from the validated input', function () {
    // Body JSON valido e query non valida: prima la validazione guardava il body e il controller la query (500).
    $this->actingAs(userWithRole('member'))->json('GET', '/calendar?month=x', ['month' => '2026-01'])->assertOk();
    $this->actingAs(userWithRole('member'))->get('/calendar?month=0000-01')->assertSessionHasErrors('month');
});

it('cannot be framed by other sites and exposes no passkey routes', function () {
    $this->get('/login')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $this->get('/passkeys/login/options')->assertNotFound();
});
