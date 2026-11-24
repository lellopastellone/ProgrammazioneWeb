<?php

use App\Models\User;
use App\Notifications\SecurityAlertNotification;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Fortify;
use PragmaRX\Google2FA\Google2FA;

/** Attiva e conferma la 2FA di $user con due codici di recupero noti; restituisce il secret in chiaro. */
function enableTwoFactor(User $user): string
{
    $secret = app(Google2FA::class)->generateSecretKey();
    $user->forceFill([
        'two_factor_secret' => Fortify::currentEncrypter()->encrypt($secret),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode(['recovery-code-1', 'recovery-code-2'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    return $secret;
}

beforeEach(function () {
    $this->organizer = userWithRole('organizer');
    $this->secret = enableTwoFactor($this->organizer);
});

it('asks for the 2FA code before the protected pages', function () {
    $this->actingAs($this->organizer)->withSession(confirmedPassword())->get('/settings/mail')
        ->assertRedirect(route('two-factor.verify'));

    $member = userWithRole('member');
    $this->actingAs($this->organizer)->withSession(confirmedPassword())->get("/users/{$member->id}/edit")
        ->assertRedirect(route('two-factor.verify'));

    $this->actingAs($this->organizer)->get('/user/verify-two-factor')->assertOk()->assertSee('Verifica e continua');
});

it('lets in after a valid code and goes back to the requested page', function () {
    $this->actingAs($this->organizer)->withSession(confirmedPassword())->get('/settings/mail');

    $this->post('/user/verify-two-factor', ['code' => app(Google2FA::class)->getCurrentOtp($this->secret)])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings/mail');

    $this->get('/settings/mail')->assertOk();
});

it('rejects a wrong code', function () {
    $this->actingAs($this->organizer)->post('/user/verify-two-factor', ['code' => '000000'])->assertSessionHasErrors('code');
    $this->post('/user/verify-two-factor', [])->assertSessionHasErrors('code');

    $this->withSession(confirmedPassword())->get('/settings/mail')->assertRedirect(route('two-factor.verify'));
});

it('accepts a recovery code once', function () {
    $this->actingAs($this->organizer)->post('/user/verify-two-factor', ['recovery_code' => 'recovery-code-1'])->assertSessionHasNoErrors();

    expect($this->organizer->fresh()->recoveryCodes())->not->toContain('recovery-code-1')->toContain('recovery-code-2');
    $this->withSession(confirmedPassword())->get('/settings/mail')->assertOk();

    $this->flushSession();
    $this->actingAs($this->organizer->fresh())->post('/user/verify-two-factor', ['recovery_code' => 'recovery-code-1'])->assertSessionHasErrors('recovery_code');
});

it('asks again once the confirmation has expired', function () {
    $expired = ['auth.two_factor_confirmed_at' => time() - config('auth.password_timeout') - 1];

    $this->actingAs($this->organizer)->withSession([...confirmedPassword(), ...$expired])->get('/settings/mail')
        ->assertRedirect(route('two-factor.verify'));
});

it('does not ask for a code to who has no 2FA', function () {
    $organizer = userWithRole('organizer');

    $this->actingAs($organizer)->withSession(confirmedPassword())->get('/settings/mail')->assertOk();
    $this->actingAs($organizer)->post('/user/verify-two-factor', ['code' => '123456'])->assertSessionHasErrors('code');
});

it('keeps the verification page for organizers only', function () {
    $this->actingAs(userWithRole('project_manager'))->get('/user/verify-two-factor')->assertForbidden();
    $this->actingAs(userWithRole('member'))->post('/user/verify-two-factor', ['code' => '123456'])->assertForbidden();
});

it('limits the code attempts', function () {
    $this->actingAs($this->organizer);

    foreach (range(1, 5) as $i) {
        $this->post('/user/verify-two-factor', ['code' => '000000'])->assertSessionHasErrors('code');
    }

    $this->post('/user/verify-two-factor', ['code' => '000000'])->assertTooManyRequests();
});

it('mails an alert when the 2FA is turned off, not when an unconfirmed setup is cancelled', function () {
    Notification::fake();

    app(DisableTwoFactorAuthentication::class)($this->organizer);
    Notification::assertSentTo($this->organizer, SecurityAlertNotification::class);

    $other = userWithRole('organizer');
    $other->forceFill(['two_factor_secret' => Fortify::currentEncrypter()->encrypt('x')])->save();
    app(DisableTwoFactorAuthentication::class)($other);
    Notification::assertNotSentTo($other, SecurityAlertNotification::class);
});
