<?php

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

it('redirects guests to login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('shows each role its own dashboard', function (string $role, string $view) {
    $this->actingAs(userWithRole($role))
        ->get('/dashboard')
        ->assertOk()
        ->assertViewIs($view);
})->with([
    'organizer' => ['organizer', 'dashboard.organizer'],
    'pm' => ['project_manager', 'dashboard.manager'],
    'member' => ['member', 'dashboard.member'],
]);

it('shows the organizer the active projects and the team workload', function () {
    $project = Project::factory()->create(['name' => 'Progetto attivo']);
    Project::factory()->create(['name' => 'Progetto archiviato', 'status' => ProjectStatus::Archived]);
    Task::factory()->for($project)->create(['end_date' => today()->subDay()]);

    $this->actingAs(userWithRole('organizer'))
        ->get('/dashboard')
        ->assertOk()
        ->assertViewHas('overdue', 1)
        ->assertSee('Progetto attivo')
        ->assertDontSee('Progetto archiviato')
        ->assertSee('Carico del team');
});

it('counts open and overdue tasks per member, only in planned or in-progress projects', function () {
    $busy = userWithRole('member');
    $idle = userWithRole('member');
    $project = projectOf(userWithRole('project_manager'), $busy, $idle);
    $archived = projectOf(userWithRole('project_manager'), $busy);
    $archived->update(['status' => ProjectStatus::Archived]);
    $finished = projectOf(userWithRole('project_manager'), $busy);
    $finished->update(['status' => ProjectStatus::Finished]);

    $tasks = collect([
        Task::factory()->for($project)->create(['end_date' => today()->subDay()]), // in ritardo
        Task::factory()->for($project)->create(), // aperto
        Task::factory()->for($project)->status(TaskStatus::Completed)->create(), // completato: non conta
        Task::factory()->for($archived)->create(['end_date' => today()->subDay()]), // progetto archiviato: non conta
        Task::factory()->for($finished)->create(), // progetto concluso: non conta
    ]);
    $busy->tasks()->attach($tasks->pluck('id'), ['assigned_at' => now()]);

    $workload = $this->actingAs(userWithRole('organizer'))->get('/dashboard')->viewData('workload');

    expect($workload->first()->is($busy))->toBeTrue()
        ->and($workload->first()->open_tasks_count)->toBe(2)
        ->and($workload->first()->overdue_tasks_count)->toBe(1)
        ->and($workload->firstWhere('id', $idle->id)->open_tasks_count)->toBe(0);
});

it('shows the team workload only on the organizer dashboard', function (string $role) {
    projectOf(userWithRole('project_manager'));

    $this->actingAs(userWithRole($role))->get('/dashboard')->assertOk()->assertDontSee('Carico del team');
})->with(['project_manager', 'member']);

it('shows the PM dashboard only with the projects they manage', function () {
    $pm = userWithRole('project_manager');
    $mine = projectOf($pm);
    $other = projectOf(userWithRole('project_manager'));
    Task::factory()->for($mine)->status(TaskStatus::InReview)->create(['title' => 'Da rivedere']);
    Task::factory()->for($other)->create(['end_date' => today()->subDay()]);

    $this->actingAs($pm)
        ->get('/dashboard')
        ->assertOk()
        ->assertViewHas('overdue', 0)
        ->assertSee($mine->name)
        ->assertSee('Da rivedere')
        ->assertDontSee($other->name);
});

it('shows the member dashboard with only the tasks assigned to me', function () {
    $member = userWithRole('member');
    $project = projectOf(userWithRole('project_manager'), $member);
    $mine = Task::factory()->for($project)->create();
    $mine->assignees()->attach($member, ['assigned_at' => now()]);
    $notMine = Task::factory()->for($project)->create();

    $this->actingAs($member)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee($mine->title)
        ->assertDontSee($notMine->title);
});

it('leaves the tasks of archived projects out of the member dashboard and calendar', function () {
    $member = userWithRole('member');
    $archived = projectOf(userWithRole('project_manager'), $member);
    $archived->update(['status' => ProjectStatus::Archived]);
    $task = Task::factory()->for($archived)->create();
    $task->assignees()->attach($member, ['assigned_at' => now()]);

    $this->actingAs($member)->get('/dashboard')->assertOk()->assertDontSee($task->title);
    $this->actingAs($member)->get('/calendar?month='.$task->end_date->format('Y-m'))->assertOk()->assertDontSee($task->title);
});

it('shows the personal calendar with my tasks in the requested month', function () {
    $member = userWithRole('member');
    $project = projectOf(userWithRole('project_manager'), $member);
    $inMonth = Task::factory()->for($project)->create(['start_date' => '2026-03-28', 'end_date' => '2026-04-02']);
    $otherMonth = Task::factory()->for($project)->create(['start_date' => '2026-05-01', 'end_date' => '2026-05-03']);
    $member->tasks()->attach([$inMonth->id, $otherMonth->id], ['assigned_at' => now()]);

    $this->actingAs($member)
        ->get('/calendar?month=2026-04')
        ->assertOk()
        ->assertSee($inMonth->title)
        ->assertDontSee($otherMonth->title);

    $this->actingAs($member)->get('/calendar?month=aprile')->assertSessionHasErrors('month');
});

it('keeps the personal calendar for members only', function (string $role) {
    $this->actingAs(userWithRole($role))->get('/calendar')->assertForbidden();
})->with(['organizer', 'project_manager']);


it('counts as overdue only open tasks with a past end date', function () {
    $project = Project::factory()->create();
    $late = Task::factory()->for($project)->create(['end_date' => today()->subDay()]);
    Task::factory()->for($project)->create(['end_date' => today()]);
    Task::factory()->for($project)->status(TaskStatus::Completed)->create(['end_date' => today()->subWeek()]);
    Task::factory()->for($project)->create(['start_date' => null, 'end_date' => null]);

    expect(Task::overdue()->pluck('id')->all())->toBe([$late->id])
        ->and(Task::all()->filter->isOverdue()->values()->modelKeys())->toBe([$late->id]);
});
