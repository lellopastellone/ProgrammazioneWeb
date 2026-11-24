<?php

use App\Models\EmailDomain;
use App\Models\MailSetting;

function smtpData(array $overrides = []): array
{
    return [
        'smtp_host' => 'smtp.azienda.it',
        'smtp_port' => 465,
        'smtp_username' => 'app',
        'smtp_password' => 'nuova',
        'smtp_from_address' => 'noreply@azienda.it',
        ...$overrides,
    ];
}

it('lets the organizer change the SMTP server', function () {
    MailSetting::create(['host' => 'vecchio', 'port' => 25, 'password' => 'vecchia', 'from_address' => 'a@azienda.it']);

    $this->actingAs(userWithRole('organizer'))->withSession(confirmedPassword());
    $this->get('/settings/mail')->assertOk()->assertSee('vecchio');
    $this->put('/settings/mail', smtpData())->assertRedirect('/settings/mail');

    expect(MailSetting::sole()->only(['host', 'port', 'username', 'password', 'from_address']))->toBe([
        'host' => 'smtp.azienda.it', 'port' => 465, 'username' => 'app', 'password' => 'nuova', 'from_address' => 'noreply@azienda.it',
    ]);
});

it('keeps the saved SMTP password when the field is left empty', function () {
    MailSetting::create(['host' => 'smtp.azienda.it', 'port' => 25, 'password' => 'segreta', 'from_address' => 'a@azienda.it']);

    $this->actingAs(userWithRole('organizer'))->withSession(confirmedPassword())
        ->put('/settings/mail', smtpData(['smtp_password' => '']))->assertRedirect();

    expect(MailSetting::sole()->password)->toBe('segreta');
});

it('creates the SMTP settings when none were saved (demo data)', function () {
    $this->actingAs(userWithRole('organizer'))->withSession(confirmedPassword())->put('/settings/mail', smtpData())->assertRedirect();

    expect(MailSetting::sole()->host)->toBe('smtp.azienda.it');
});

it('requires a sender in a company domain', function () {
    EmailDomain::create(['domain' => 'azienda.it']);

    $this->actingAs(userWithRole('organizer'))->withSession(confirmedPassword())
        ->put('/settings/mail', smtpData(['smtp_from_address' => 'noreply@gmail.com']))
        ->assertSessionHasErrors('smtp_from_address');

    expect(MailSetting::exists())->toBeFalse();
});

it('forbids the SMTP settings to non organizers', function (string $role) {
    $this->actingAs(userWithRole($role))->get('/settings/mail')->assertForbidden();
    $this->actingAs(userWithRole($role))->put('/settings/mail', smtpData())->assertForbidden();

    expect(MailSetting::exists())->toBeFalse();
})->with(['project_manager', 'member']);

it('asks the organizer to confirm the password before showing or changing the SMTP server', function () {
    $this->actingAs(userWithRole('organizer'));

    $this->get('/settings/mail')->assertRedirect(route('password.confirm'));
    $this->put('/settings/mail', smtpData())->assertRedirect(route('password.confirm'));

    expect(MailSetting::exists())->toBeFalse();
});

it('never sends the saved SMTP password to a new host', function () {
    MailSetting::create(['host' => 'smtp.azienda.it', 'port' => 587, 'password' => 'segreta', 'from_address' => 'a@azienda.it']);
    $this->actingAs(userWithRole('organizer'))->withSession(confirmedPassword());

    $this->put('/settings/mail', smtpData(['smtp_host' => 'smtp.attaccante.it', 'smtp_password' => '']))->assertSessionHasErrors('smtp_password');
    expect(MailSetting::sole()->host)->toBe('smtp.azienda.it');

    $this->put('/settings/mail', smtpData(['smtp_host' => 'smtp2.azienda.it', 'smtp_password' => 'altra']))->assertSessionHasNoErrors();
    expect(MailSetting::sole()->only(['host', 'password']))->toBe(['host' => 'smtp2.azienda.it', 'password' => 'altra']);
});

it('accepts only SMTP ports', function (int $port, bool $valid) {
    $response = $this->actingAs(userWithRole('organizer'))->withSession(confirmedPassword())
        ->put('/settings/mail', smtpData(['smtp_port' => $port]));

    $valid ? $response->assertSessionHasNoErrors() : $response->assertSessionHasErrors('smtp_port');
})->with([[25, true], [587, true], [1025, true], [22, false], [6379, false]]);
