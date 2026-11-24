<?php

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Iteration;
use App\Models\Task;

function taskData(array $overrides = []): array
{
    return [
        'title' => 'Implementare il login',
        'description' => 'desc',
        'start_date' => '2026-10-02',
        'end_date' => '2026-10-05',
        'priority' => 'high',
        ...$overrides,
    ];
}

it('lets project members see all tasks, highlighting their own', function () {
    $member = userWithRole('member');
    $project = projectOf(userWithRole('project_manager'), $member);
    $mine = Task::factory()->for($project)->create();
    $mine->assignees()->attach($member, ['assigned_at' => now()]);
    $other = Task::factory()->for($project)->create();

    $response = $this->actingAs($member)->get("/projects/{$project->id}/tasks")
        ->assertOk()->assertSee($mine->title)->assertSee($other->title);

    $tasks = $response->viewData('tasks')->keyBy('id');
    expect($tasks->every->relationLoaded('assignees'))->toBeTrue()
        ->and($tasks[$mine->id]->isAssignedTo($member))->toBeTrue()
        ->and($tasks[$other->id]->isAssignedTo($member))->toBeFalse();

    $this->actingAs($member)->get("/tasks/{$other->id}")->assertOk();
});

it('forbids tasks of other projects', function () {
    $project = projectOf(userWithRole('project_manager'));
    $task = Task::factory()->for($project)->create();
    $outsider = userWithRole('member');

    $this->actingAs($outsider)->get("/projects/{$project->id}/tasks")->assertForbidden();
    $this->actingAs($outsider)->get("/tasks/{$task->id}")->assertForbidden();
});

it('filters the backlog', function () {
    $pm = userWithRole('project_manager');
    $member = userWithRole('member');
    $project = projectOf($pm, $member);
    $iteration = Iteration::factory()->for($project)->create();
    $match = Task::factory()->for($project)->status(TaskStatus::InProgress)->create(['priority' => 'high', 'iteration_id' => $iteration->id]);
    $match->assignees()->attach($member, ['assigned_at' => now()]);
    $noMatch = Task::factory()->for($project)->create(['priority' => 'high']);

    $query = http_build_query(['status' => 'in_progress', 'priority' => 'high', 'assignee' => $member->id, 'iteration' => $iteration->id]);

    $this->actingAs($pm)->get("/projects/{$project->id}/tasks?{$query}")
        ->assertOk()->assertSee($match->title)->assertDontSee($noMatch->title);

    $this->actingAs($pm)->get("/projects/{$project->id}/tasks?status=boh")->assertSessionHasErrors('status');
});

it('lets only the project PM create, update and delete tasks', function () {
    $pm = userWithRole('project_manager');
    $member = userWithRole('member');
    $project = projectOf($pm, $member);

    $this->actingAs($pm)->get("/projects/{$project->id}/tasks/create")->assertOk();
    $this->actingAs($pm)->post("/projects/{$project->id}/tasks", taskData())->assertRedirect();
    $task = $project->tasks()->sole();
    expect($task->status)->toBe(TaskStatus::Todo);

    $this->actingAs($pm)->put("/tasks/{$task->id}", taskData(['title' => 'Nuovo titolo']))->assertRedirect();
    expect($task->fresh()->title)->toBe('Nuovo titolo');

    foreach ([$member, userWithRole('project_manager'), userWithRole('organizer')] as $user) {
        $this->actingAs($user)->post("/projects/{$project->id}/tasks", taskData())->assertForbidden();
        $this->actingAs($user)->put("/tasks/{$task->id}", taskData())->assertForbidden();
        $this->actingAs($user)->delete("/tasks/{$task->id}")->assertForbidden();
    }

    $this->actingAs($pm)->delete("/tasks/{$task->id}")->assertRedirect();
    expect(Task::find($task->id))->toBeNull();
});

it('never changes the status through the generic update', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $task = Task::factory()->for($project)->create();

    $this->actingAs($pm)->put("/tasks/{$task->id}", taskData(['status' => 'completed']))->assertRedirect();
    expect($task->fresh()->status)->toBe(TaskStatus::Todo);
});

it('keeps task dates inside the chosen iteration', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $iteration = Iteration::factory()->for($project)->create(['starts_on' => '2026-10-01', 'ends_on' => '2026-10-14']);

    $this->actingAs($pm)
        ->post("/projects/{$project->id}/tasks", taskData(['iteration_id' => $iteration->id, 'end_date' => '2026-10-20']))
        ->assertSessionHasErrors('end_date');

    $this->actingAs($pm)
        ->post("/projects/{$project->id}/tasks", taskData(['iteration_id' => $iteration->id]))
        ->assertSessionDoesntHaveErrors();
});

it('rejects an iteration of another project', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $foreignIteration = Iteration::factory()->create();

    $this->actingAs($pm)
        ->post("/projects/{$project->id}/tasks", taskData(['iteration_id' => $foreignIteration->id]))
        ->assertSessionHasErrors('iteration_id');
});

it('lets the assignee move the task forward but not complete it', function () {
    $member = userWithRole('member');
    $project = projectOf(userWithRole('project_manager'), $member);
    $task = Task::factory()->for($project)->create();
    $task->assignees()->attach($member, ['assigned_at' => now()]);

    $this->actingAs($member)->patch("/tasks/{$task->id}/status", ['status' => 'in_progress'])->assertSessionDoesntHaveErrors();
    $this->actingAs($member)->patch("/tasks/{$task->id}/status", ['status' => 'in_review'])->assertSessionDoesntHaveErrors();
    $this->actingAs($member)->patch("/tasks/{$task->id}/status", ['status' => 'completed'])->assertSessionHasErrors('status');

    expect($task->fresh()->status)->toBe(TaskStatus::InReview);
});

it('starts a planned project when its first task goes in progress', function () {
    $member = userWithRole('member');
    $project = projectOf(userWithRole('project_manager'), $member);
    $project->update(['status' => ProjectStatus::Planned]);
    $task = Task::factory()->for($project)->create();
    $task->assignees()->attach($member, ['assigned_at' => now()]);

    $this->actingAs($member)->patch("/tasks/{$task->id}/status", ['status' => 'in_progress'])->assertSessionDoesntHaveErrors();

    expect($project->fresh()->status)->toBe(ProjectStatus::InProgress);
});

it('keeps a planned project planned when its PM is deactivated, while members keep working', function () {
    $member = userWithRole('member');
    $pm = userWithRole('project_manager');
    $project = projectOf($pm, $member);
    $project->update(['status' => ProjectStatus::Planned]);
    $task = Task::factory()->for($project)->create();
    $task->assignees()->attach($member, ['assigned_at' => now()]);
    $pm->update(['active' => false]);

    $this->actingAs($member)->patch("/tasks/{$task->id}/status", ['status' => 'in_progress'])->assertSessionDoesntHaveErrors();

    expect($task->fresh()->status)->toBe(TaskStatus::InProgress)
        ->and($project->fresh()->status)->toBe(ProjectStatus::Planned);
});

it('lets the PM complete a task and sets completed_at', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $task = Task::factory()->for($project)->status(TaskStatus::InReview)->create();

    $this->actingAs($pm)->patch("/tasks/{$task->id}/status", ['status' => 'completed'])->assertSessionDoesntHaveErrors();

    expect($task->fresh()->status)->toBe(TaskStatus::Completed)
        ->and($task->fresh()->completed_at)->not->toBeNull();
});

it('lets the PM change the status only of a task in review', function (TaskStatus $from, string $to) {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $task = Task::factory()->for($project)->status($from)->create();

    $this->actingAs($pm)->patch("/tasks/{$task->id}/status", ['status' => $to])->assertSessionHasErrors('status');

    expect($task->fresh()->status)->toBe($from);
})->with([
    'da fare → in corso' => [TaskStatus::Todo, 'in_progress'],
    'in corso → in review' => [TaskStatus::InProgress, 'in_review'],
]);

it('rejects invalid transitions', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $task = Task::factory()->for($project)->create();

    $this->actingAs($pm)->patch("/tasks/{$task->id}/status", ['status' => 'completed'])->assertSessionHasErrors('status');
    expect($task->fresh()->status)->toBe(TaskStatus::Todo);
});

it('forbids status changes to non-assignees', function () {
    $member = userWithRole('member');
    $project = projectOf(userWithRole('project_manager'), $member);
    $task = Task::factory()->for($project)->create();

    $this->actingAs($member)->patch("/tasks/{$task->id}/status", ['status' => 'in_progress'])->assertForbidden();
    $this->actingAs(userWithRole('organizer'))->patch("/tasks/{$task->id}/status", ['status' => 'in_progress'])->assertForbidden();
});

it('lets the PM assign only members of the project group', function () {
    $pm = userWithRole('project_manager');
    $member = userWithRole('member');
    $project = projectOf($pm, $member);
    $task = Task::factory()->for($project)->create();

    $this->actingAs($pm)->post("/tasks/{$task->id}/assignees", ['user_id' => $member->id])->assertSessionDoesntHaveErrors();
    expect($task->isAssignedTo($member))->toBeTrue();

    $assignedAt = $task->assignees()->first()->pivot->assigned_at;
    $this->travel(1)->hours();
    $this->actingAs($pm)->post("/tasks/{$task->id}/assignees", ['user_id' => $member->id])->assertSessionHasErrors('user_id');
    expect($task->assignees()->first()->pivot->assigned_at)->toEqual($assignedAt);

    $this->actingAs($pm)->post("/tasks/{$task->id}/assignees", ['user_id' => userWithRole('member')->id])->assertSessionHasErrors('user_id');

    $this->actingAs($pm)->delete("/tasks/{$task->id}/assignees/{$member->id}")->assertRedirect();
    expect($task->isAssignedTo($member))->toBeFalse();
});

it('does not offer or accept a deactivated project member as assignee', function () {
    $pm = userWithRole('project_manager');
    $member = userWithRole('member');
    $project = projectOf($pm, $member);
    $task = Task::factory()->for($project)->create();
    $member->update(['active' => false]);

    $this->actingAs($pm)->get("/tasks/{$task->id}")
        ->assertOk()
        ->assertViewHas('candidates', fn ($candidates) => $candidates->isEmpty())
        ->assertViewHas('hasMembers', false);

    $this->actingAs($pm)->post("/tasks/{$task->id}/assignees", ['user_id' => $member->id])->assertSessionHasErrors('user_id');
    expect($task->assignees()->exists())->toBeFalse();
});

it('forbids assignments to anyone but the project PM', function () {
    $member = userWithRole('member');
    $project = projectOf(userWithRole('project_manager'), $member);
    $task = Task::factory()->for($project)->create();

    $this->actingAs($member)->post("/tasks/{$task->id}/assignees", ['user_id' => $member->id])->assertForbidden();
    $this->actingAs(userWithRole('project_manager'))->delete("/tasks/{$task->id}/assignees/{$member->id}")->assertForbidden();
});

it('requires both task dates, with the end not before the start', function (array $data, string $field) {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $task = Task::factory()->for($project)->create();

    $this->actingAs($pm)->post("/projects/{$project->id}/tasks", taskData($data))->assertSessionHasErrors($field);
    $this->actingAs($pm)->put("/tasks/{$task->id}", taskData($data))->assertSessionHasErrors($field);
})->with([
    'senza inizio' => [['start_date' => null], 'start_date'],
    'senza fine' => [['end_date' => null], 'end_date'],
    'fine prima dell\'inizio' => [['start_date' => '2026-10-05', 'end_date' => '2026-10-02'], 'end_date'],
]);

it('lets the PM put a task without iteration into an iteration from the edit form', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $iteration = Iteration::factory()->for($project)->create(['starts_on' => '2026-10-01', 'ends_on' => '2026-10-14']);
    $task = Task::factory()->for($project)->create(['iteration_id' => null]);

    $this->actingAs($pm)->put("/tasks/{$task->id}", taskData(['iteration_id' => $iteration->id, 'end_date' => '2026-10-20']))
        ->assertSessionHasErrors('end_date');
    expect($task->fresh()->iteration_id)->toBeNull();

    $this->actingAs($pm)->put("/tasks/{$task->id}", taskData(['iteration_id' => $iteration->id]))
        ->assertSessionDoesntHaveErrors();
    expect($task->fresh()->iteration_id)->toBe($iteration->id);
});

it('checks the new dates against the current iteration when the update omits iteration_id', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $iteration = Iteration::factory()->for($project)->create(['starts_on' => '2026-10-01', 'ends_on' => '2026-10-14']);
    $task = Task::factory()->for($project)->create(['iteration_id' => $iteration->id, 'start_date' => '2026-10-02', 'end_date' => '2026-10-05']);

    $this->actingAs($pm)->put("/tasks/{$task->id}", taskData(['end_date' => '2026-10-20']))
        ->assertSessionHasErrors('end_date');
    expect($task->fresh()->end_date->toDateString())->toBe('2026-10-05');

    $this->actingAs($pm)->put("/tasks/{$task->id}", taskData(['end_date' => '2026-10-10']))
        ->assertSessionDoesntHaveErrors();
    $task->refresh();
    expect($task->end_date->toDateString())->toBe('2026-10-10')
        ->and($task->iteration_id)->toBe($iteration->id);
});

it('rejects a duplicate task title within the project, ignoring case', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    Task::factory()->for($project)->create(['title' => 'Implementare il login']);
    $task = Task::factory()->for($project)->create(['title' => 'Altro']);
    Task::factory()->create(['title' => 'Stesso titolo, altro progetto']);

    $this->actingAs($pm)->post("/projects/{$project->id}/tasks", taskData())->assertSessionHasErrors('title');
    $this->actingAs($pm)->post("/projects/{$project->id}/tasks", taskData(['title' => 'implementare IL LOGIN']))->assertSessionHasErrors('title');
    $this->actingAs($pm)->put("/tasks/{$task->id}", taskData())->assertSessionHasErrors('title');

    // Lo stesso titolo in un altro progetto va bene, e un task tiene il proprio titolo in modifica.
    $this->actingAs($pm)->post("/projects/{$project->id}/tasks", taskData(['title' => 'Stesso titolo, altro progetto']))->assertSessionDoesntHaveErrors();
    $this->actingAs($pm)->put("/tasks/{$task->id}", taskData(['title' => 'ALTRO']))->assertSessionDoesntHaveErrors();
});

it('forbids task operations on archived or finished projects', function () {
    $pm = userWithRole('project_manager');
    $member = userWithRole('member');
    $project = projectOf($pm, $member);
    $task = Task::factory()->for($project)->create();
    $task->assignees()->attach($member, ['assigned_at' => now()]);

    foreach ([\App\Enums\ProjectStatus::Archived, \App\Enums\ProjectStatus::Finished] as $status) {
        $project->update(['status' => $status]);

        // Creazione task bloccata
        $this->actingAs($pm)->get("/projects/{$project->id}/tasks/create")->assertForbidden();
        $this->actingAs($pm)->post("/projects/{$project->id}/tasks", taskData(['title' => 'Nuovo ' . $status->value]))->assertForbidden();

        // Modifica ed eliminazione task bloccate
        $this->actingAs($pm)->get("/tasks/{$task->id}/edit")->assertForbidden();
        $this->actingAs($pm)->put("/tasks/{$task->id}", taskData(['title' => 'Cambiato']))->assertForbidden();
        $this->actingAs($pm)->delete("/tasks/{$task->id}")->assertForbidden();

        // Cambio stato del task bloccato
        $this->actingAs($member)->patch("/tasks/{$task->id}/status", ['status' => 'in_progress'])->assertForbidden();

        // Assegnatari bloccati
        $this->actingAs($pm)->post("/tasks/{$task->id}/assignees", ['user_id' => $member->id])->assertForbidden();
        $this->actingAs($pm)->delete("/tasks/{$task->id}/assignees/{$member->id}")->assertForbidden();

        // Commenti bloccati
        $this->actingAs($member)->post("/tasks/{$task->id}/comments", ['text' => 'Commento'])->assertForbidden();
    }
});

it('does not let an assignee who no longer sees the project change the status', function () {
    $member = userWithRole('member');
    $project = projectOf(userWithRole('project_manager'), $member);
    $task = Task::factory()->for($project)->create();
    $task->assignees()->attach($member, ['assigned_at' => now()]);

    // Assegnazione rimasta (es. corsa tra rimozione dal gruppo e assegnazione), ma il member non è più nel progetto.
    $project->members()->detach($member);

    $this->actingAs($member)->patch("/tasks/{$task->id}/status", ['status' => 'in_progress'])->assertForbidden();
    expect($task->fresh()->status)->toBe(TaskStatus::Todo);
});
