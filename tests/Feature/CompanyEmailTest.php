<?php

use App\Models\EmailDomain;
use App\Models\User;

function newUserData(string $email): array
{
    return ['name' => 'Mario', 'surname' => 'Rossi', 'email' => $email, 'role' => 'member'];
}

it('accepts any email when no company domain is configured', function () {
    $this->actingAs(userWithRole('organizer'))->post('/users', newUserData('mario@gmail.com'))->assertSessionDoesntHaveErrors();
});

it('lets the organizer create users only with a company email, ignoring case', function () {
    EmailDomain::create(['domain' => 'azienda.it']);
    $organizer = userWithRole('organizer');

    $this->actingAs($organizer)->post('/users', newUserData('mario@gmail.com'))->assertSessionHasErrors('email');
    expect(User::firstWhere('email', 'mario@gmail.com'))->toBeNull();

    $this->actingAs($organizer)->post('/users', newUserData('Mario@Azienda.IT'))->assertSessionDoesntHaveErrors();
    // Salvata in minuscolo: Fortify cerca il login in minuscolo e su PostgreSQL il confronto distingue le maiuscole.
    expect(User::where('email', 'mario@azienda.it')->exists())->toBeTrue();
});

it('keeps an unchanged email of another domain but rejects a new one when editing a user', function () {
    EmailDomain::create(['domain' => 'azienda.it']);
    $organizer = userWithRole('organizer');
    $user = userWithRole('member');
    $user->update(['email' => 'vecchia@altro.it']);
    $data = fn (string $email) => ['name' => 'Nuovo', 'surname' => $user->surname, 'email' => $email];
    $this->withSession(confirmedPassword());

    $this->actingAs($organizer)->put("/users/{$user->id}", $data('vecchia@altro.it'))->assertSessionDoesntHaveErrors();
    $this->actingAs($organizer)->put("/users/{$user->id}", $data('nuova@gmail.com'))->assertSessionHasErrors('email');
    $this->actingAs($organizer)->put("/users/{$user->id}", $data('nuova@azienda.it'))->assertSessionDoesntHaveErrors();

    expect($user->fresh()->email)->toBe('nuova@azienda.it');
});

it('applies the company domains to the Jetstream profile too', function () {
    EmailDomain::create(['domain' => 'azienda.it']);
    $member = userWithRole('member');
    $member->update(['email' => 'mario@azienda.it']);
    $data = fn (string $email) => ['name' => $member->name, 'surname' => $member->surname, 'email' => $email, 'current_password' => 'password'];

    $this->actingAs($member)->put('/user/profile-information', $data('mario@gmail.com'))
        ->assertSessionHasErrorsIn('updateProfileInformation', 'email');
    expect($member->fresh()->email)->toBe('mario@azienda.it');

    $this->actingAs($member)->put('/user/profile-information', $data('mario.rossi@azienda.it'))
        ->assertSessionHasNoErrors();
    expect($member->fresh()->email)->toBe('mario.rossi@azienda.it');
});
