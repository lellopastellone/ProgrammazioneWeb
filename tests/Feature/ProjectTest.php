<?php

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;

it('lists only the projects visible to the user', function () {
    $pm = userWithRole('project_manager');
    $member = userWithRole('member');
    $managed = projectOf($pm);
    $joined = projectOf(userWithRole('project_manager'), $member);
    $foreign = projectOf(userWithRole('project_manager'));

    $this->actingAs($pm)->get('/projects')
        ->assertOk()->assertSee($managed->name)->assertDontSee($joined->name)->assertDontSee($foreign->name);

    $this->actingAs($member)->get('/projects')
        ->assertOk()->assertSee($joined->name)->assertDontSee($managed->name)->assertDontSee($foreign->name);

    $this->actingAs(userWithRole('organizer'))->get('/projects')
        ->assertOk()->assertSee($managed->name)->assertSee($joined->name)->assertSee($foreign->name);
});

it('splits the project list into active, finished and archived tabs', function () {
    $organizer = userWithRole('organizer');
    Project::factory()->create(['name' => 'Progetto pianificato', 'status' => ProjectStatus::Planned]);
    Project::factory()->create(['name' => 'Progetto concluso', 'status' => ProjectStatus::Finished]);
    Project::factory()->create(['name' => 'Progetto archiviato', 'status' => ProjectStatus::Archived]);

    $this->actingAs($organizer)->get('/projects')
        ->assertSee('Progetto pianificato')->assertDontSee('Progetto concluso')->assertDontSee('Progetto archiviato');
    $this->actingAs($organizer)->get('/projects?status=finished')
        ->assertSee('Progetto concluso')->assertDontSee('Progetto pianificato')->assertDontSee('Progetto archiviato');
    $this->actingAs($organizer)->get('/projects?status=archived')
        ->assertSee('Progetto archiviato')->assertDontSee('Progetto concluso');
});

it('lets only the organizer create projects', function () {
    $organizer = userWithRole('organizer');
    $pm = userWithRole('project_manager');

    $this->actingAs($organizer)->get('/projects/create')->assertOk();
    $this->actingAs($organizer)
        ->post('/projects', ['name' => 'Nuovo', 'description' => 'desc', 'pm_id' => $pm->id])
        ->assertRedirect();

    $project = Project::firstWhere('name', 'Nuovo');
    expect($project->created_by)->toBe($organizer->id)
        ->and($project->pm_id)->toBe($pm->id)
        ->and($project->status)->toBe(ProjectStatus::Planned);

    $this->actingAs($pm)->get('/projects/create')->assertForbidden();
    $this->actingAs($pm)->post('/projects', ['name' => 'Altro'])->assertForbidden();
});

it('rejects a pm_id that is not an active project manager', function () {
    $organizer = userWithRole('organizer');
    $inactivePm = userWithRole('project_manager');
    $inactivePm->update(['active' => false]);

    foreach ([userWithRole('member')->id, $inactivePm->id, 999] as $pmId) {
        $this->actingAs($organizer)
            ->post('/projects', ['name' => 'X', 'pm_id' => $pmId])
            ->assertSessionHasErrors('pm_id');
    }
});

it('shows a project to organizer, its PM and its members only', function () {
    $pm = userWithRole('project_manager');
    $member = userWithRole('member');
    $project = projectOf($pm, $member);

    foreach ([userWithRole('organizer'), $pm, $member] as $user) {
        $this->actingAs($user)->get("/projects/{$project->id}")->assertOk()->assertSee($project->name);
    }

    $this->actingAs(userWithRole('member'))->get("/projects/{$project->id}")->assertForbidden();
    $this->actingAs(userWithRole('project_manager'))->get("/projects/{$project->id}")->assertForbidden();
});

it('lets only the project PM edit it', function () {
    $pm = userWithRole('project_manager');
    $member = userWithRole('member');
    $project = projectOf($pm, $member);

    $this->actingAs($pm)->get("/projects/{$project->id}/edit")->assertOk();
    $this->actingAs($pm)->put("/projects/{$project->id}", ['name' => 'Rinominato'])->assertRedirect();
    expect($project->fresh()->name)->toBe('Rinominato');

    $this->actingAs(userWithRole('project_manager'))->put("/projects/{$project->id}", ['name' => 'X'])->assertForbidden();
    $this->actingAs($member)->put("/projects/{$project->id}", ['name' => 'X'])->assertForbidden();
    // L'organizer vede tutto ma in sola lettura.
    $this->actingAs(userWithRole('organizer'))->put("/projects/{$project->id}", ['name' => 'X'])->assertForbidden();
});

it('lets only the project PM change status, including archiving', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);

    $this->actingAs($pm)->patch("/projects/{$project->id}/status", ['status' => 'archived'])->assertRedirect();
    expect($project->fresh()->status)->toBe(ProjectStatus::Archived);

    $this->actingAs($pm)->patch("/projects/{$project->id}/status", ['status' => 'deleted'])->assertSessionHasErrors('status');
    $this->actingAs(userWithRole('organizer'))->patch("/projects/{$project->id}/status", ['status' => 'finished'])->assertForbidden();
});

it('finishes a project only when all its tasks are completed', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $task = Task::factory()->for($project)->status(TaskStatus::InReview)->create();

    $this->actingAs($pm)->patch("/projects/{$project->id}/status", ['status' => 'finished'])->assertSessionHasErrors('status');
    expect($project->fresh()->status)->not->toBe(ProjectStatus::Finished);

    $task->forceFill(['status' => TaskStatus::Completed, 'completed_at' => now()])->save();

    $this->actingAs($pm)->patch("/projects/{$project->id}/status", ['status' => 'finished'])->assertSessionHasNoErrors();
    expect($project->fresh()->status)->toBe(ProjectStatus::Finished);
});

it('lets only the organizer assign the manager', function () {
    $project = projectOf(userWithRole('project_manager'));
    $newPm = userWithRole('project_manager');

    $this->actingAs(userWithRole('organizer'))
        ->patch("/projects/{$project->id}/manager", ['pm_id' => $newPm->id])
        ->assertRedirect();
    expect($project->fresh()->pm_id)->toBe($newPm->id);

    $this->actingAs($newPm)->patch("/projects/{$project->id}/manager", ['pm_id' => $newPm->id])->assertForbidden();
});

it('forbids changing the manager of a finished or archived project', function (ProjectStatus $status) {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $project->update(['status' => $status]);

    $this->actingAs(userWithRole('organizer'))
        ->patch("/projects/{$project->id}/manager", ['pm_id' => userWithRole('project_manager')->id])
        ->assertForbidden();
    expect($project->fresh()->pm_id)->toBe($pm->id);
})->with([ProjectStatus::Finished, ProjectStatus::Archived]);

it('never brings a started project back to planned', function (ProjectStatus $from) {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $project->update(['status' => $from]);

    $this->actingAs($pm)->patch("/projects/{$project->id}/status", ['status' => 'planned'])->assertSessionHasErrors('status');
    expect($project->fresh()->status)->toBe($from);
})->with([ProjectStatus::InProgress, ProjectStatus::Finished, ProjectStatus::Archived]);

it('keeps a planned project planned on request', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $project->update(['status' => ProjectStatus::Planned]);

    $this->actingAs($pm)->patch("/projects/{$project->id}/status", ['status' => 'planned'])->assertSessionHasNoErrors();
});

it('requires a unique project name, ignoring case', function () {
    $organizer = userWithRole('organizer');
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $project->update(['name' => 'Alpha', 'status' => ProjectStatus::Archived]);

    $this->actingAs($organizer)->post('/projects', ['name' => 'ALPHA'])->assertSessionHasErrors('name');

    $other = projectOf($pm);
    $this->actingAs($pm)->put("/projects/{$other->id}", ['name' => 'alpha'])->assertSessionHasErrors('name');
    $this->actingAs($pm)->put("/projects/{$other->id}", ['name' => $other->name])->assertSessionHasNoErrors();
});

it('lets only the organizer delete a project without a manager', function () {
    $organizer = userWithRole('organizer');
    $pm = userWithRole('project_manager');
    $orphan = Project::factory()->create(['created_by' => $organizer->id]);
    $managed = projectOf($pm);

    $this->actingAs($pm)->delete("/projects/{$orphan->id}")->assertForbidden();
    $this->actingAs($organizer)->delete("/projects/{$managed->id}")->assertForbidden();
    $this->actingAs($pm)->delete("/projects/{$managed->id}")->assertForbidden();

    $this->actingAs($organizer)->get("/projects/{$orphan->id}")->assertSee('Elimina progetto');
    $this->actingAs($organizer)->get("/projects/{$managed->id}")->assertDontSee('Elimina progetto');

    $this->actingAs($organizer)->delete("/projects/{$orphan->id}")->assertRedirect(route('projects.index'));
    expect(Project::find($orphan->id))->toBeNull()
        ->and(Project::find($managed->id))->not->toBeNull();
});

it('never lets the organizer delete a project that had a PM, even once deactivated', function () {
    $organizer = userWithRole('organizer');
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);

    $pm->update(['active' => false]);

    $this->actingAs($organizer)->get("/projects/{$project->id}")->assertSee('Archivia progetto')->assertDontSee('Elimina progetto');
    $this->actingAs($organizer)->delete("/projects/{$project->id}")->assertForbidden();
    expect(Project::find($project->id))->not->toBeNull();
});

it('lets the organizer archive a project without an active PM', function () {
    $organizer = userWithRole('organizer');
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $project->update(['status' => ProjectStatus::InProgress]);

    $this->actingAs($organizer)->get("/projects/{$project->id}")->assertDontSee('Archivia progetto');
    $this->actingAs($organizer)->patch("/projects/{$project->id}/status", ['status' => 'archived'])->assertForbidden();

    $pm->update(['active' => false]);

    $this->actingAs($organizer)->get("/projects/{$project->id}")->assertSee('Archivia progetto');
    $this->actingAs(userWithRole('project_manager'))->patch("/projects/{$project->id}/status", ['status' => 'archived'])->assertForbidden();
    $this->actingAs($organizer)->patch("/projects/{$project->id}/status", ['status' => 'planned'])->assertSessionHasErrors('status');
    expect($project->fresh()->status)->toBe(ProjectStatus::InProgress);

    $this->actingAs($organizer)->patch("/projects/{$project->id}/status", ['status' => 'archived'])->assertSessionHasNoErrors();
    expect($project->fresh()->status)->toBe(ProjectStatus::Archived);
    $this->actingAs($organizer)->get("/projects/{$project->id}")->assertDontSee('Archivia progetto')->assertSee('Riattiva progetto')->assertDontSee('Elimina progetto');
});

it('lets the organizer finish a project without an active PM once all its tasks are completed', function () {
    $organizer = userWithRole('organizer');
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $project->update(['status' => ProjectStatus::InProgress]);
    Task::factory()->for($project)->status(TaskStatus::Completed)->create(['completed_at' => now()]);
    $open = Task::factory()->for($project)->status(TaskStatus::InReview)->create();

    // Con un PM attivo lo stato lo decide lui.
    $this->actingAs($organizer)->patch("/projects/{$project->id}/status", ['status' => 'finished'])->assertForbidden();

    $pm->update(['active' => false]);

    $this->actingAs($organizer)->get("/projects/{$project->id}")->assertDontSee('Concludi progetto');
    $this->actingAs($organizer)->patch("/projects/{$project->id}/status", ['status' => 'finished'])->assertSessionHasErrors('status');
    expect($project->fresh()->status)->toBe(ProjectStatus::InProgress);

    $open->forceFill(['status' => TaskStatus::Completed, 'completed_at' => now()])->save();

    $this->actingAs($organizer)->get("/projects/{$project->id}")->assertSee('Concludi progetto');
    $this->actingAs($organizer)->patch("/projects/{$project->id}/status", ['status' => 'finished'])->assertSessionHasNoErrors();
    expect($project->fresh()->status)->toBe(ProjectStatus::Finished);
    $this->actingAs($organizer)->get("/projects/{$project->id}")->assertDontSee('Concludi progetto')->assertSee('Riattiva progetto');

    $this->actingAs($organizer)->patch("/projects/{$project->id}/status", ['status' => 'archived'])->assertSessionHasNoErrors();
    expect($project->fresh()->status)->toBe(ProjectStatus::Archived);
});

it('lets the organizer reopen a finished project without an active PM and assign a new one', function () {
    $organizer = userWithRole('organizer');
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $project->update(['status' => ProjectStatus::Finished]);

    $this->actingAs($organizer)->patch("/projects/{$project->id}/status", ['status' => 'in_progress'])->assertForbidden();

    $pm->update(['active' => false]);

    $this->actingAs($organizer)->get("/projects/{$project->id}")->assertSee('Riattiva progetto')->assertSee('Archivia progetto');
    $this->actingAs($organizer)->patch("/projects/{$project->id}/manager", ['pm_id' => userWithRole('project_manager')->id])->assertForbidden();

    $this->actingAs($organizer)->patch("/projects/{$project->id}/status", ['status' => 'in_progress'])->assertSessionHasNoErrors();
    expect($project->fresh()->status)->toBe(ProjectStatus::InProgress);

    $newPm = userWithRole('project_manager');
    $this->actingAs($organizer)->patch("/projects/{$project->id}/manager", ['pm_id' => $newPm->id])->assertSessionHasNoErrors();
    expect($project->fresh()->pm_id)->toBe($newPm->id);
});

it('never shows the finish button on a project without tasks', function () {
    $organizer = userWithRole('organizer');
    $orphan = Project::factory()->create(['created_by' => $organizer->id]);

    $this->actingAs($organizer)->get("/projects/{$orphan->id}")->assertSee('Archivia progetto')->assertDontSee('Concludi progetto');
});

it('lets the organizer reopen an archived project without an active PM and assign a new one', function () {
    $organizer = userWithRole('organizer');
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $project->update(['status' => ProjectStatus::Archived]);

    $this->actingAs($organizer)->patch("/projects/{$project->id}/status", ['status' => 'in_progress'])->assertForbidden();

    $pm->update(['active' => false]);

    $this->actingAs($organizer)->get("/projects/{$project->id}")->assertSee('Riattiva progetto')->assertSee('Disattivato');
    $this->actingAs($organizer)->patch("/projects/{$project->id}/status", ['status' => 'planned'])->assertSessionHasErrors('status');
    $this->actingAs($organizer)->patch("/projects/{$project->id}/manager", ['pm_id' => userWithRole('project_manager')->id])->assertForbidden();

    $this->actingAs($organizer)->patch("/projects/{$project->id}/status", ['status' => 'in_progress'])->assertSessionHasNoErrors();
    expect($project->fresh()->status)->toBe(ProjectStatus::InProgress);

    $newPm = userWithRole('project_manager');
    $this->actingAs($organizer)->patch("/projects/{$project->id}/manager", ['pm_id' => $newPm->id])->assertSessionHasNoErrors();
    expect($project->fresh()->pm_id)->toBe($newPm->id);

    // Con un PM attivo l'organizer torna in sola lettura sullo stato.
    $this->actingAs($organizer)->patch("/projects/{$project->id}/status", ['status' => 'archived'])->assertForbidden();
});

it('never lets the organizer start a planned project without an active PM', function () {
    $organizer = userWithRole('organizer');
    $orphan = Project::factory()->create(['created_by' => $organizer->id]);
    $pm = userWithRole('project_manager');
    $abandoned = projectOf($pm);
    $pm->update(['active' => false]);

    foreach ([$orphan, $abandoned] as $project) {
        $project->update(['status' => ProjectStatus::Planned]);

        $this->actingAs($organizer)->patch("/projects/{$project->id}/status", ['status' => 'in_progress'])->assertSessionHasErrors('status');
        expect($project->fresh()->status)->toBe(ProjectStatus::Planned);
    }
});

it('lets the PM add and remove members, dropping their task assignments', function () {
    $pm = userWithRole('project_manager');
    $member = userWithRole('member');
    $project = projectOf($pm);

    $this->actingAs($pm)->get("/projects/{$project->id}/members")->assertOk();
    $this->actingAs($pm)->post("/projects/{$project->id}/members", ['user_id' => $member->id])->assertRedirect();
    expect($project->hasMember($member))->toBeTrue();

    $task = Task::factory()->for($project)->create();
    $task->assignees()->attach($member, ['assigned_at' => now()]);

    $this->actingAs($pm)->delete("/projects/{$project->id}/members/{$member->id}")->assertRedirect();
    expect($project->hasMember($member))->toBeFalse()
        ->and($task->isAssignedTo($member))->toBeFalse();
});

it('forbids other users from managing members', function () {
    $member = userWithRole('member');
    $project = projectOf(userWithRole('project_manager'), $member);

    $this->actingAs($member)->get("/projects/{$project->id}/members")->assertOk();
    $this->actingAs($member)->post("/projects/{$project->id}/members", ['user_id' => userWithRole('member')->id])->assertForbidden();
    $this->actingAs(userWithRole('project_manager'))->delete("/projects/{$project->id}/members/{$member->id}")->assertForbidden();
});

it('shows the project members emails only to the organizer', function () {
    $member = userWithRole('member');
    $project = projectOf($pm = userWithRole('project_manager'), $member);

    $this->actingAs($pm)->get("/projects/{$project->id}/members")->assertOk()->assertSee($member->surname)->assertDontSee($member->email);
    $this->actingAs(userWithRole('organizer'))->get("/projects/{$project->id}/members")->assertOk()->assertSee($member->email);
});

it('only adds active members to the project group', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);

    $this->actingAs($pm)
        ->post("/projects/{$project->id}/members", ['user_id' => userWithRole('organizer')->id])
        ->assertSessionHasErrors('user_id');

    $inactive = userWithRole('member');
    $inactive->update(['active' => false]);
    $this->actingAs($pm)
        ->post("/projects/{$project->id}/members", ['user_id' => $inactive->id])
        ->assertSessionHasErrors('user_id');
    expect($project->members()->exists())->toBeFalse();
});

it('shows the project calendar with all the tasks of the month', function () {
    $member = userWithRole('member');
    $project = projectOf(userWithRole('project_manager'), $member);
    $mine = Task::factory()->for($project)->create(['start_date' => '2026-04-10', 'end_date' => '2026-04-12']);
    $mine->assignees()->attach($member, ['assigned_at' => now()]);
    $other = Task::factory()->for($project)->create(['start_date' => '2026-04-20', 'end_date' => null]);
    $outside = Task::factory()->for($project)->create(['start_date' => '2026-06-01', 'end_date' => '2026-06-02']);

    $this->actingAs($member)->get("/projects/{$project->id}/calendar?month=2026-04")
        ->assertOk()
        ->assertSee($mine->title)
        ->assertSee($other->title)
        ->assertDontSee($outside->title);

    $this->actingAs(userWithRole('member'))->get("/projects/{$project->id}/calendar")->assertForbidden();
});

it('forbids editing details and members when the project is archived or finished', function () {
    $pm = userWithRole('project_manager');
    $member = userWithRole('member');
    $project = projectOf($pm, $member);

    foreach ([ProjectStatus::Archived, ProjectStatus::Finished] as $status) {
        $project->update(['status' => $status]);

        // La pagina di edit (impostazioni) si apre per permettere il cambio stato...
        $this->actingAs($pm)->get("/projects/{$project->id}/edit")->assertOk();

        // ...ma il salvataggio dei dati generali e la gestione membri sono bloccati (403)
        $this->actingAs($pm)->put("/projects/{$project->id}", ['name' => 'Nuovo nome'])->assertForbidden();
        $this->actingAs($pm)->post("/projects/{$project->id}/members", ['user_id' => userWithRole('member')->id])->assertForbidden();
        $this->actingAs($pm)->delete("/projects/{$project->id}/members/{$member->id}")->assertForbidden();
    }
});

it('allows reactivating an archived project and re-enables modifications', function () {
    $pm = userWithRole('project_manager');
    $member = userWithRole('member');
    $newMember = userWithRole('member');
    $project = projectOf($pm, $member);
    $project->update(['status' => ProjectStatus::Archived]);

    // Il PM può riattivare il progetto
    $this->actingAs($pm)->patch("/projects/{$project->id}/status", ['status' => ProjectStatus::InProgress->value])->assertRedirect();
    expect($project->fresh()->status)->toBe(ProjectStatus::InProgress);

    // Ora le modifiche e la gestione membri sono di nuovo permesse
    $this->actingAs($pm)->put("/projects/{$project->id}", ['name' => 'Riattivato'])->assertRedirect();
    $this->actingAs($pm)->post("/projects/{$project->id}/members", ['user_id' => $newMember->id])->assertRedirect();
    expect($project->fresh()->name)->toBe('Riattivato')
        ->and($project->fresh()->hasMember($newMember))->toBeTrue();
});
