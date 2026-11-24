<?php

use App\Models\MailSetting;
use App\Models\Team;
use App\Models\User;
use App\Notifications\EmailChangedNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Notification;

it('lets the organizer create a user who gets a welcome link', function () {
    Notification::fake();
    $team = Team::factory()->create();

    $this->actingAs(userWithRole('organizer'))
        ->post('/users', [
            'name' => 'Ada', 'surname' => 'Lovelace', 'email' => 'ada@example.com',
            'role' => 'member', 'teams' => [$team->id],
        ])
        ->assertRedirect();

    $user = User::firstWhere('email', 'ada@example.com');
    expect($user->getRoleNames()->all())->toBe(['member'])
        ->and($user->teams->modelKeys())->toBe([$team->id])
        ->and($user->active)->toBeTrue();
    Notification::assertSentTo($user, WelcomeNotification::class);
});

it('creates the user and warns when the SMTP server is unreachable', function () {
    MailSetting::create(['host' => '127.0.0.1', 'port' => 1, 'from_address' => 'noreply@example.com']);

    $this->actingAs(userWithRole('organizer'))
        ->post('/users', ['name' => 'Ada', 'surname' => 'Lovelace', 'email' => 'ada@example.com', 'role' => 'member'])
        ->assertRedirect()
        ->assertSessionHas('flash.bannerStyle', 'warning');

    expect(User::where('email', 'ada@example.com')->exists())->toBeTrue();
});

it('forbids user creation to non organizers', function () {
    $this->actingAs(userWithRole('project_manager'))->get('/users/create')->assertForbidden();
    $this->actingAs(userWithRole('member'))
        ->post('/users', ['name' => 'X', 'surname' => 'Y', 'email' => 'x@example.com', 'role' => 'member'])
        ->assertForbidden();
});

it('validates the role on user creation', function (mixed $role) {
    $this->actingAs(userWithRole('organizer'))
        ->post('/users', ['name' => 'X', 'surname' => 'Y', 'email' => 'x@example.com', 'role' => $role])
        ->assertSessionHasErrors('role');

    expect(User::firstWhere('email', 'x@example.com'))->toBeNull();
})->with([
    'ruolo inesistente' => ['admin'],
    'nessun ruolo' => [null],
    'più ruoli' => [['project_manager', 'member']],
]);

it('lets the organizer update a user and change their teams', function () {
    $user = userWithRole('member');
    [$backend, $frontend] = Team::factory(2)->create();
    $user->teams()->attach($backend);

    $this->actingAs(userWithRole('organizer'))->withSession(confirmedPassword())
        ->put("/users/{$user->id}", ['name' => 'Nuovo', 'surname' => $user->surname, 'email' => $user->email, 'teams' => [$frontend->id]])
        ->assertRedirect();

    expect($user->fresh()->name)->toBe('Nuovo')
        ->and($user->fresh()->teams->modelKeys())->toBe([$frontend->id]);
});

it('never changes roles after creation', function () {
    $organizer = userWithRole('organizer');
    $user = userWithRole('member');
    $pm = userWithRole('project_manager');

    foreach ([$user, $pm] as $target) {
        $this->actingAs($organizer)->withSession(confirmedPassword())
            ->put("/users/{$target->id}", ['name' => 'X', 'surname' => 'Y', 'email' => $target->email, 'role' => 'project_manager'])
            ->assertSessionHasErrors('role');
    }

    expect($user->fresh()->getRoleNames()->all())->toBe(['member'])
        ->and($pm->fresh()->getRoleNames()->all())->toBe(['project_manager']);
});

it('shows the user list to PMs without emails', function () {
    $member = userWithRole('member');

    $this->actingAs(userWithRole('project_manager'))->get('/users')
        ->assertOk()->assertSee($member->surname)->assertDontSee($member->email);

    $this->actingAs(userWithRole('organizer'))->get('/users')->assertOk()->assertSee($member->email);
    $this->actingAs(userWithRole('member'))->get('/users')->assertForbidden();
});

it('shows the team members to PMs without emails', function () {
    $member = userWithRole('member');
    $team = Team::factory()->create();
    $team->users()->attach($member);

    $this->actingAs(userWithRole('project_manager'))->get("/teams/{$team->id}")
        ->assertOk()->assertSee($member->surname)->assertDontSee($member->email);

    $this->actingAs(userWithRole('organizer'))->get("/teams/{$team->id}")->assertOk()->assertSee($member->email);
});

it('lets the organizer deactivate and reactivate users, but not themselves or other organizers', function () {
    $organizer = userWithRole('organizer');
    $user = userWithRole('member');

    $this->actingAs($organizer)->patch("/users/{$user->id}/active")->assertRedirect();
    expect($user->fresh()->active)->toBeFalse();
    $this->actingAs($organizer)->patch("/users/{$user->id}/active")->assertRedirect();
    expect($user->fresh()->active)->toBeTrue();

    $this->actingAs($organizer)->patch("/users/{$organizer->id}/active")->assertForbidden();
    $other = userWithRole('organizer');
    $this->actingAs($organizer)->patch("/users/{$other->id}/active")->assertForbidden();
    expect($organizer->fresh()->active)->toBeTrue()->and($other->fresh()->active)->toBeTrue();
    $this->actingAs(userWithRole('project_manager'))->patch("/users/{$user->id}/active")->assertForbidden();
});

it('logs out a user deactivated while logged in', function () {
    $user = userWithRole('member');
    $user->update(['active' => false]);

    $this->actingAs($user)->get('/dashboard')->assertRedirect(route('login'));
    $this->assertGuest('web');
});

it('rejects login of inactive users', function () {
    $user = User::factory()->inactive()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'password']);

    $this->assertGuest();
});

it('manages teams as organizer and forbids deleting non-empty teams', function () {
    $organizer = userWithRole('organizer');
    $member = userWithRole('member');

    $this->actingAs($organizer)->post('/teams', ['name' => 'Backend'])->assertRedirect();
    $team = Team::firstWhere('name', 'Backend');

    $this->actingAs($organizer)->post('/teams', ['name' => 'backend'])->assertSessionHasErrors('name');
    $this->actingAs($organizer)->put("/teams/{$team->id}", ['name' => 'Backend', 'description' => 'API'])->assertRedirect();

    $this->actingAs($organizer)->post("/teams/{$team->id}/members", ['user_id' => $member->id])->assertRedirect();
    $this->actingAs($organizer)->delete("/teams/{$team->id}")->assertSessionHasErrors('team');
    expect(Team::find($team->id))->not->toBeNull();

    $this->actingAs($organizer)->delete("/teams/{$team->id}/members/{$member->id}")->assertRedirect();
    $this->actingAs($organizer)->delete("/teams/{$team->id}")->assertRedirect(route('teams.index'));
    expect(Team::find($team->id))->toBeNull();
});

it('lets PMs read teams but not manage them', function () {
    $pm = userWithRole('project_manager');
    $team = Team::factory()->create();

    $this->actingAs($pm)->get('/teams')->assertOk()->assertSee($team->name);
    $this->actingAs($pm)->post('/teams', ['name' => 'X'])->assertForbidden();
    $this->actingAs($pm)->delete("/teams/{$team->id}")->assertForbidden();
    $this->actingAs(userWithRole('member'))->get('/teams')->assertForbidden();
});

it('keeps organizers and project managers out of teams', function (string $role) {
    $organizer = userWithRole('organizer');
    $user = userWithRole($role);
    $team = Team::factory()->create();

    // Creazione con un team.
    $this->actingAs($organizer)
        ->post('/users', ['name' => 'Pia', 'surname' => 'Rossi', 'email' => 'pia@example.com', 'role' => $role, 'teams' => [$team->id]])
        ->assertSessionHasErrors('teams');
    expect(User::firstWhere('email', 'pia@example.com'))->toBeNull();

    // Aggiunta dalla pagina del team: rifiutata, e l'utente non compare tra i candidati.
    $this->actingAs($organizer)->post("/teams/{$team->id}/members", ['user_id' => $user->id])->assertSessionHasErrors('user_id');
    expect($team->users()->exists())->toBeFalse();
    $this->actingAs($organizer)->get("/teams/{$team->id}")->assertOk()->assertDontSee("value=\"{$user->id}\"", false);
})->with(['organizer', 'project_manager']);

it('rejects deactivated members when adding to a team', function () {
    $organizer = userWithRole('organizer');
    $member = userWithRole('member');
    $member->update(['active' => false]);
    $team = Team::factory()->create();

    $this->actingAs($organizer)->post("/teams/{$team->id}/members", ['user_id' => $member->id])->assertSessionHasErrors('user_id');
    expect($team->users()->exists())->toBeFalse();
});

it('rejects teams when editing a project manager', function () {
    $organizer = userWithRole('organizer');
    $pm = userWithRole('project_manager');
    $team = Team::factory()->create();

    $this->actingAs($organizer)->withSession(confirmedPassword())
        ->put("/users/{$pm->id}", ['name' => $pm->name, 'surname' => $pm->surname, 'email' => $pm->email, 'teams' => [$team->id]])
        ->assertSessionHasErrors('teams');
    $this->actingAs($organizer)
        ->put("/users/{$pm->id}", ['name' => 'Nuovo', 'surname' => $pm->surname, 'email' => $pm->email])
        ->assertSessionDoesntHaveErrors();
    expect($pm->fresh()->teams)->toBeEmpty();
});

it('shows the activation toggle only on the user edit page', function () {
    $organizer = userWithRole('organizer');
    $user = userWithRole('member');
    $action = route('users.active.update', $user);

    $this->actingAs($organizer)->get('/users')->assertOk()->assertDontSee($action);
    $this->actingAs($organizer)->get("/users/{$user->id}")->assertOk()->assertDontSee($action);
    $this->actingAs($organizer)->withSession(confirmedPassword())->get("/users/{$user->id}/edit")->assertOk()->assertSee($action);
});

it('sends the organizer to the Jetstream profile to edit their own user', function () {
    $organizer = userWithRole('organizer');
    $other = userWithRole('member');

    $this->actingAs($organizer)->withSession(confirmedPassword())->get("/users/{$organizer->id}/edit")->assertRedirect(route('profile.show'));
    $this->actingAs($organizer)->get("/users/{$organizer->id}")->assertOk()->assertSee(route('profile.show'))->assertDontSee(route('users.edit', $organizer));
    $this->actingAs($organizer)->get("/users/{$other->id}")->assertOk()->assertSee(route('users.edit', $other));
});

it('lets only the organizer edit other users', function (string $role) {
    $user = userWithRole('member');

    $this->actingAs(userWithRole($role))->get("/users/{$user->id}/edit")->assertForbidden();
    $this->actingAs(userWithRole($role))
        ->put("/users/{$user->id}", ['name' => 'X', 'surname' => 'Y', 'email' => $user->email])
        ->assertForbidden();
    expect($user->fresh()->name)->not->toBe('X');
})->with(['project_manager', 'member']);

it('forbids organizers to edit other organizers or themselves through /users', function () {
    $organizer = userWithRole('organizer');
    $other = userWithRole('organizer');
    $data = fn (User $u) => ['name' => 'X', 'surname' => 'Y', 'email' => $u->email];

    $this->actingAs($organizer)->get("/users/{$other->id}/edit")->assertForbidden();
    $this->actingAs($organizer)->put("/users/{$other->id}", $data($other))->assertForbidden();
    $this->actingAs($organizer)->put("/users/{$organizer->id}", $data($organizer))->assertForbidden();
    expect($other->fresh()->name)->not->toBe('X')->and($organizer->fresh()->name)->not->toBe('X');

    // Nel profilo di un altro organizer non c'è il pulsante Modifica.
    $this->actingAs($organizer)->get("/users/{$other->id}")->assertOk()->assertDontSee(route('users.edit', $other));
});

it('logs out a deactivated user from the Jetstream profile too', function () {
    $user = userWithRole('member');
    $user->update(['active' => false]);

    $this->actingAs($user)->get('/user/profile')->assertRedirect(route('login'));
    $this->assertGuest('web');
});

it('asks the organizer to confirm the password before editing a user', function () {
    $user = userWithRole('member');
    $this->actingAs(userWithRole('organizer'));

    $this->get("/users/{$user->id}/edit")->assertRedirect(route('password.confirm'));
    $this->put("/users/{$user->id}", ['name' => 'X', 'surname' => 'Y', 'email' => $user->email])->assertRedirect(route('password.confirm'));
    expect($user->fresh()->name)->not->toBe('X');

    $this->withSession(confirmedPassword())->get("/users/{$user->id}/edit")->assertOk();
});

it('tells the old address when the organizer changes the email of a user', function () {
    Notification::fake();
    $user = userWithRole('project_manager');
    $old = $user->email;

    $this->actingAs(userWithRole('organizer'))->withSession(confirmedPassword())
        ->put("/users/{$user->id}", ['name' => $user->name, 'surname' => $user->surname, 'email' => 'nuova@example.com'])
        ->assertSessionHasNoErrors();

    Notification::assertSentOnDemand(EmailChangedNotification::class, fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === $old);
});

it('does not tell anyone when the email stays the same', function () {
    Notification::fake();
    $user = userWithRole('member');

    $this->actingAs(userWithRole('organizer'))->withSession(confirmedPassword())
        ->put("/users/{$user->id}", ['name' => 'Nuovo', 'surname' => $user->surname, 'email' => $user->email]);

    Notification::assertNothingSent();
});
