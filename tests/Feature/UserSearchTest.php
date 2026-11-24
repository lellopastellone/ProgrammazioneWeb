<?php

use App\Models\Task;
use App\Models\Team;
use App\Models\User;

/** Member attivo con nome e cognome scelti. */
function memberNamed(string $name, string $surname): User
{
    return User::factory()->create(['name' => $name, 'surname' => $surname, 'email' => strtolower("{$name}.{$surname}@example.com")])->assignRole('member');
}

beforeEach(function () {
    $this->luca = memberNamed('Luca', 'Conti');
    $this->elena = memberNamed('Elena', 'Ferrari');
});

it('filters the team candidates by every word, ignoring case', function () {
    $team = Team::factory()->create();

    $this->actingAs(userWithRole('organizer'))->get("/teams/{$team->id}/edit?q=LUCA con")
        ->assertOk()->assertSee('Luca Conti')->assertDontSee('Elena Ferrari');
});

it('filters the project member candidates', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm);

    $this->actingAs($pm)->get("/projects/{$project->id}/members?q=ferr")
        ->assertOk()->assertSee('Elena Ferrari')->assertDontSee('Luca Conti');
});

it('filters the members that can be assigned to a task', function () {
    $pm = userWithRole('project_manager');
    $task = Task::factory()->for(projectOf($pm, $this->luca, $this->elena))->create();

    $this->actingAs($pm)->get("/tasks/{$task->id}?q=elena")
        ->assertOk()->assertSee('Elena Ferrari')->assertDontSee('Luca Conti');
});

it('searches the user list, by email only for the organizer', function () {
    $this->actingAs(userWithRole('organizer'))->get('/users?q=luca.conti@')
        ->assertOk()->assertSee('Luca Conti')->assertDontSee('Elena Ferrari');

    $this->actingAs(userWithRole('project_manager'))->get('/users?q=luca.conti@')
        ->assertOk()->assertDontSee('Luca Conti');
});

it('paginates the user list', function () {
    User::factory(30)->create();

    $this->actingAs(userWithRole('organizer'))->get('/users')->assertOk()->assertSee('page=2');
});

it('shows at most 20 candidates and asks to refine the search', function () {
    User::factory(25)->create()->each->assignRole('member');
    $team = Team::factory()->create();

    $this->actingAs(userWithRole('organizer'))->get("/teams/{$team->id}/edit")
        ->assertOk()->assertSee('Mostrati i primi 20');
});

it('ignores a search term that is not a string', function () {
    $pm = userWithRole('project_manager');
    $project = projectOf($pm, $this->luca);
    $task = Task::factory()->for($project)->create();
    $organizer = userWithRole('organizer');

    $this->actingAs($organizer)->get('/teams/'.Team::factory()->create()->id.'/edit?q[]=x')->assertOk();
    $this->actingAs($organizer)->get('/users?q[]=x')->assertOk();
    $this->actingAs($pm)->get("/projects/{$project->id}/members?q[]=x")->assertOk();
    $this->actingAs($pm)->get("/tasks/{$task->id}?q[]=x")->assertOk();
});
