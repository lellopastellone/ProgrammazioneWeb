<?php

use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

function welcomeToken(User $user): string
{
    return Password::broker('invites')->createToken($user);
}

it('invalidates the welcome link once the password is recovered', function () {
    $user = userWithRole('member');
    $invite = welcomeToken($user);

    $this->post('/reset-password', [
        'token' => Password::broker('users')->createToken($user), 'email' => $user->email,
        'password' => 'una-password-sicura', 'password_confirmation' => 'una-password-sicura',
    ])->assertSessionHasNoErrors();

    expect(Password::broker('invites')->tokenExists($user, $invite))->toBeFalse();
});

it('sends a welcome mail with the link to choose the password', function () {
    $user = userWithRole('member');
    $mail = (new WelcomeNotification('abc'))->toMail($user);

    expect($mail->subject)->toContain('Benvenuto')
        ->and((string) $mail->render())
        ->toContain('Scegli la password')
        ->toContain(route('password.set', ['token' => 'abc', 'email' => $user->email]))
        ->toContain('#4F46E5') // tema con la palette
        ->not->toContain('reset');
});

it('sends the password recovery mail in italian with the same style', function () {
    $user = userWithRole('member');
    $html = (string) (new ResetPassword('abc'))->toMail($user)->render();

    expect($html)->toContain('Recupero password')
        ->toContain(route('password.reset', ['token' => 'abc', 'email' => $user->email]))
        ->toContain('#4F46E5');
});

it('shows the welcome page to choose the password', function () {
    $this->get('/set-password/abc?email=a@example.com')->assertOk()->assertSee('Benvenuto!');
});

it('sets the first password from the welcome link', function () {
    $user = userWithRole('member');

    $this->post('/set-password', [
        'token' => welcomeToken($user), 'email' => $user->email,
        'password' => 'una-password-sicura', 'password_confirmation' => 'una-password-sicura',
    ])->assertRedirect(route('login'))->assertSessionHas('status');

    expect(Hash::check('una-password-sicura', $user->fresh()->password))->toBeTrue();
});

it('rejects an invalid or already used welcome link', function () {
    $user = userWithRole('member');
    $data = ['email' => $user->email, 'password' => 'una-password-sicura', 'password_confirmation' => 'una-password-sicura'];

    $this->post('/set-password', [...$data, 'token' => 'sbagliato'])->assertSessionHasErrors('email');

    $token = welcomeToken($user);
    $this->post('/set-password', [...$data, 'token' => $token])->assertRedirect(route('login'));
    $this->post('/set-password', [...$data, 'token' => $token])->assertSessionHasErrors('email');
});

it('keeps the welcome link valid for a week', function () {
    $user = userWithRole('member');
    $token = welcomeToken($user);

    $this->travel(6)->days();

    $this->post('/set-password', [
        'token' => $token, 'email' => $user->email,
        'password' => 'una-password-sicura', 'password_confirmation' => 'una-password-sicura',
    ])->assertRedirect(route('login'));
});

it('builds the mail links on APP_URL, not on the Host header of the request', function () {
    $user = userWithRole('member');
    URL::forceRootUrl('http://attacker.test'); // come una richiesta arrivata con "Host: attacker.test"

    expect((new ResetPassword('abc'))->toMail($user)->viewData['url'])->toStartWith(config('app.url').'/reset-password/abc')
        ->and((new WelcomeNotification('abc'))->toMail($user)->viewData['url'])->toStartWith(config('app.url').'/set-password/abc');
});

it('does not accept a password recovery token as a welcome token', function () {
    $user = userWithRole('member');

    $this->post('/set-password', [
        'token' => Password::broker('users')->createToken($user), 'email' => $user->email,
        'password' => 'una-password-sicura', 'password_confirmation' => 'una-password-sicura',
    ])->assertSessionHasErrors('email');

    expect(Hash::check('una-password-sicura', $user->fresh()->password))->toBeFalse();
});

it('keeps the welcome link valid when someone asks for a password recovery', function () {
    Notification::fake();
    $user = userWithRole('member');
    $token = welcomeToken($user);

    $this->post('/forgot-password', ['email' => $user->email]);

    expect(Password::broker('invites')->tokenExists($user, $token))->toBeTrue();
});

it('does not turn the user name into a link in the mail', function () {
    $user = userWithRole('member');
    $user->name = '[Clicca qui](https://attacker.test)';

    expect((string) (new WelcomeNotification('abc'))->toMail($user)->render())->not->toContain('href="https://attacker.test"');
});

it('does not send the password changed alert on the first password', function () {
    $user = userWithRole('member');
    Notification::fake();

    $this->post('/set-password', [
        'token' => welcomeToken($user), 'email' => $user->email,
        'password' => 'una-password-sicura', 'password_confirmation' => 'una-password-sicura',
    ])->assertRedirect(route('login'));

    Notification::assertNothingSentTo($user);
});
