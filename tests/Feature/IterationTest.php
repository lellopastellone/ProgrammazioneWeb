<?php

use App\Models\Iteration;
use App\Models\Task;

// Le date delle iterazioni non possono essere nel passato: i test lavorano con "oggi" fisso al 15 settembre 2026.
beforeEach(fn () => $this->travelTo('2026-09-15'));

it('lets the PM create, update and delete iterations', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);

    $this->actingAs($pm)
        ->post("/projects/{$project->id}/iterations", ['name' => 'Sprint 1', 'starts_on' => '2026-10-01', 'ends_on' => '2026-10-14'])
        ->assertRedirect();
    $iteration = $project->iterations()->sole();

    $this->actingAs($pm)
        ->put("/iterations/{$iteration->id}", ['name' => 'Sprint 1b', 'starts_on' => '2026-10-01', 'ends_on' => '2026-10-21'])
        ->assertRedirect();
    expect($iteration->fresh()->name)->toBe('Sprint 1b');

    $task = Task::factory()->for($project)->create(['iteration_id' => $iteration->id, 'start_date' => '2026-10-02', 'end_date' => '2026-10-03']);
    $this->actingAs($pm)->delete("/iterations/{$iteration->id}")->assertRedirect();

    expect(Iteration::find($iteration->id))->toBeNull()
        ->and($task->fresh()->iteration_id)->toBeNull();
});

it('forbids iteration management to anyone but the project PM', function () {
    $member = userWithRole('member');
    $project = projectOf(userWithRole('project_manager'), $member);
    $iteration = Iteration::factory()->for($project)->create();
    $data = ['name' => 'X', 'starts_on' => '2026-10-01', 'ends_on' => '2026-10-14'];

    foreach ([$member, userWithRole('project_manager'), userWithRole('organizer')] as $user) {
        $this->actingAs($user)->post("/projects/{$project->id}/iterations", $data)->assertForbidden();
        $this->actingAs($user)->put("/iterations/{$iteration->id}", $data)->assertForbidden();
        $this->actingAs($user)->delete("/iterations/{$iteration->id}")->assertForbidden();
    }
});

it('rejects an iteration that ends before it starts', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);

    $this->actingAs($pm)
        ->post("/projects/{$project->id}/iterations", ['name' => 'X', 'starts_on' => '2026-10-14', 'ends_on' => '2026-10-01'])
        ->assertSessionHasErrors('ends_on');
});

it('rejects new iteration dates that would leave tasks outside', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $iteration = Iteration::factory()->for($project)->create(['starts_on' => '2026-10-01', 'ends_on' => '2026-10-14']);
    Task::factory()->for($project)->create(['iteration_id' => $iteration->id, 'start_date' => '2026-10-10', 'end_date' => '2026-10-12']);

    $this->actingAs($pm)
        ->put("/iterations/{$iteration->id}", ['name' => 'X', 'starts_on' => '2026-10-01', 'ends_on' => '2026-10-11'])
        ->assertSessionHasErrors('starts_on');

    $this->actingAs($pm)
        ->put("/iterations/{$iteration->id}", ['name' => 'X', 'starts_on' => '2026-10-10', 'ends_on' => '2026-10-12'])
        ->assertSessionDoesntHaveErrors();
});

it('rejects new iteration dates before today', function (string $starts, string $ends, string $field) {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);

    $this->actingAs($pm)
        ->post("/projects/{$project->id}/iterations", ['name' => 'X', 'starts_on' => $starts, 'ends_on' => $ends])
        ->assertSessionHasErrors($field);

    expect($project->iterations()->exists())->toBeFalse();
})->with([
    'inizio ieri' => ['2026-09-14', '2026-09-30', 'starts_on'],
    'tutta nel passato' => ['2026-09-01', '2026-09-10', 'starts_on'],
]);

it('accepts an iteration starting today', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);

    $this->actingAs($pm)
        ->post("/projects/{$project->id}/iterations", ['name' => 'X', 'starts_on' => '2026-09-15', 'ends_on' => '2026-09-15'])
        ->assertSessionDoesntHaveErrors();
});

it('lets the PM edit an iteration already started, but not move its dates into the past', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $iteration = Iteration::factory()->for($project)->create(['starts_on' => '2026-09-08', 'ends_on' => '2026-09-21']);

    // Inizio già passato ma invariato: si può rinominare e allungare.
    $this->actingAs($pm)
        ->put("/iterations/{$iteration->id}", ['name' => 'Sprint lungo', 'starts_on' => '2026-09-08', 'ends_on' => '2026-09-28'])
        ->assertSessionDoesntHaveErrors();

    // Spostare l'inizio su un altro giorno passato o la fine nel passato no.
    $this->actingAs($pm)
        ->put("/iterations/{$iteration->id}", ['name' => 'X', 'starts_on' => '2026-09-01', 'ends_on' => '2026-09-28'])
        ->assertSessionHasErrors('starts_on');
    $this->actingAs($pm)
        ->put("/iterations/{$iteration->id}", ['name' => 'X', 'starts_on' => '2026-09-08', 'ends_on' => '2026-09-12'])
        ->assertSessionHasErrors('ends_on');

    expect($iteration->fresh())
        ->name->toBe('Sprint lungo')
        ->ends_on->toDateString()->toBe('2026-09-28');
});

it('rejects iterations that overlap another iteration of the same project', function (string $starts, string $ends) {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    Iteration::factory()->for($project)->create(['name' => 'Sprint 1', 'starts_on' => '2026-10-01', 'ends_on' => '2026-10-14']);

    $this->actingAs($pm)
        ->post("/projects/{$project->id}/iterations", ['name' => 'Sprint 2', 'starts_on' => $starts, 'ends_on' => $ends])
        ->assertSessionHasErrors('starts_on');

    expect($project->iterations()->count())->toBe(1);
})->with([
    'stesso giorno di fine' => ['2026-10-14', '2026-10-20'],
    'contenuta' => ['2026-10-05', '2026-10-06'],
    'che la contiene' => ['2026-09-20', '2026-10-30'],
]);

it('allows adjacent iterations and iterations of other projects on the same dates', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    $iteration = Iteration::factory()->for($project)->create(['name' => 'Sprint 1', 'starts_on' => '2026-10-01', 'ends_on' => '2026-10-14']);
    Iteration::factory()->create(['name' => 'Sprint 2', 'starts_on' => '2026-10-15', 'ends_on' => '2026-10-28']);

    $this->actingAs($pm)
        ->post("/projects/{$project->id}/iterations", ['name' => 'Sprint 2', 'starts_on' => '2026-10-15', 'ends_on' => '2026-10-28'])
        ->assertSessionDoesntHaveErrors();

    // In modifica l'iterazione non si scontra con sé stessa.
    $this->actingAs($pm)
        ->put("/iterations/{$iteration->id}", ['name' => 'Sprint 1', 'starts_on' => '2026-10-01', 'ends_on' => '2026-10-10'])
        ->assertSessionDoesntHaveErrors();

    // Ma non può allargarsi sulla successiva.
    $this->actingAs($pm)
        ->put("/iterations/{$iteration->id}", ['name' => 'Sprint 1', 'starts_on' => '2026-10-01', 'ends_on' => '2026-10-15'])
        ->assertSessionHasErrors('starts_on');
});

it('rejects a duplicate iteration name within the project, ignoring case', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);
    Iteration::factory()->for($project)->create(['name' => 'Sprint 1', 'starts_on' => '2026-10-01', 'ends_on' => '2026-10-14']);
    $other = Iteration::factory()->for($project)->create(['name' => 'Sprint 2', 'starts_on' => '2026-10-15', 'ends_on' => '2026-10-28']);

    $this->actingAs($pm)
        ->post("/projects/{$project->id}/iterations", ['name' => 'Sprint 1', 'starts_on' => '2026-11-01', 'ends_on' => '2026-11-14'])
        ->assertSessionHasErrors('name');
    $this->actingAs($pm)
        ->post("/projects/{$project->id}/iterations", ['name' => 'SPRINT 1', 'starts_on' => '2026-11-01', 'ends_on' => '2026-11-14'])
        ->assertSessionHasErrors('name');
    $this->actingAs($pm)
        ->put("/iterations/{$other->id}", ['name' => 'Sprint 1', 'starts_on' => '2026-10-15', 'ends_on' => '2026-10-28'])
        ->assertSessionHasErrors('name');
});
