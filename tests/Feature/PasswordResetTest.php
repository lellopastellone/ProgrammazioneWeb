<?php

use App\Models\MailSetting;
use App\Models\User;
use App\Notifications\SecurityAlertNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Features;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
})->skip(function () {
    return ! Features::enabled(Features::resetPasswords());
}, 'Password updates are not enabled.');

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->post('/forgot-password', [
        'email' => $user->email,
    ]);

    Notification::assertSentTo($user, ResetPassword::class);
})->skip(function () {
    return ! Features::enabled(Features::resetPasswords());
}, 'Password updates are not enabled.');

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->post('/forgot-password', [
        'email' => $user->email,
    ]);

    Notification::assertSentTo($user, ResetPassword::class, function (object $notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
})->skip(function () {
    return ! Features::enabled(Features::resetPasswords());
}, 'Password updates are not enabled.');

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->post('/forgot-password', [
        'email' => $user->email,
    ]);

    Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors();

        return true;
    });
})->skip(function () {
    return ! Features::enabled(Features::resetPasswords());
}, 'Password updates are not enabled.');

test('the recovery form answers the same for unknown emails', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status', trans(Password::RESET_LINK_SENT));
    $this->post('/forgot-password', ['email' => 'nessuno@example.com'])
        ->assertSessionHasNoErrors()->assertSessionHas('status', trans(Password::RESET_LINK_SENT));
    // Seconda richiesta entro un minuto: il throttle del broker scatta solo per le email esistenti, la risposta non cambia.
    $this->post('/forgot-password', ['email' => $user->email])
        ->assertSessionHasNoErrors()->assertSessionHas('status', trans(Password::RESET_LINK_SENT));
});

test('the recovery form answers the same when the SMTP server is unreachable', function () {
    MailSetting::create(['host' => '127.0.0.1', 'port' => 1, 'from_address' => 'noreply@example.com']);
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email])
        ->assertSessionHasNoErrors()->assertSessionHas('status', trans(Password::RESET_LINK_SENT));
});

test('a reset with an unknown email fails like a wrong token', function () {
    $user = User::factory()->create();
    $data = ['token' => 'sbagliato', 'password' => 'password', 'password_confirmation' => 'password'];

    $wrongToken = trans(Password::INVALID_TOKEN);

    $this->post('/reset-password', [...$data, 'email' => $user->email])->assertSessionHasErrors(['email' => $wrongToken]);
    $this->post('/reset-password', [...$data, 'email' => 'nessuno@example.com'])->assertSessionHasErrors(['email' => $wrongToken]);
});

test('password recovery requests are rate limited per IP', function () {
    Notification::fake();

    foreach (range(1, 10) as $i) {
        $this->post('/forgot-password', ['email' => "utente{$i}@example.com"])->assertRedirect();
    }

    $this->post('/forgot-password', ['email' => 'altro@example.com'])->assertTooManyRequests();
    $this->post('/set-password', ['email' => 'altro@example.com'])->assertTooManyRequests();
    $this->get('/forgot-password')->assertOk(); // la pagina resta raggiungibile
});

test('a password reset mails a security alert', function () {
    $user = User::factory()->create();
    $token = Password::broker('users')->createToken($user);
    Notification::fake();

    $this->post('/reset-password', ['token' => $token, 'email' => $user->email, 'password' => 'una-password-sicura', 'password_confirmation' => 'una-password-sicura'])
        ->assertSessionHasNoErrors();

    Notification::assertSentTo($user, SecurityAlertNotification::class);
});
