<?php

use App\Models\EmailDomain;
use App\Models\MailSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    // Installazione vuota: via l'organizer creato da TestCase.
    User::role('organizer')->get()->each->delete();
});

function setupData(array $overrides = []): array
{
    return [
        'name' => 'Olivia',
        'surname' => 'Owner',
        'email' => 'owner@example.com',
        'domains' => 'example.com',
        'password' => 'una-password-sicura',
        'password_confirmation' => 'una-password-sicura',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_username' => 'app',
        'smtp_password' => 'segreta',
        'smtp_from_address' => 'noreply@example.com',
        ...$overrides,
    ];
}

it('sends every request to the setup while there is no organizer', function (string $uri) {
    $this->get($uri)->assertRedirect(route('setup'));
})->with(['/', '/login', '/forgot-password', '/dashboard', '/projects']);

it('sends logged-in users to the setup too', function () {
    $this->actingAs(userWithRole('member'))->get('/dashboard')->assertRedirect(route('setup'));
});

it('shows the setup form', function () {
    $this->get('/setup')->assertOk()->assertSee('Configurazione iniziale');
});

it('creates the first organizer, logs them in and completes the setup', function () {
    $this->post('/setup', setupData())->assertRedirect(route('dashboard'));

    $user = User::firstWhere('email', 'owner@example.com');
    expect($user->hasRole('organizer'))->toBeTrue()
        ->and(Hash::check('una-password-sicura', $user->password))->toBeTrue()
        ->and(User::hasOrganizer())->toBeTrue();
    $this->assertAuthenticatedAs($user);
});

it('creates roles and permissions when no seeder was run', function () {
    Role::query()->delete();
    Permission::query()->delete();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->post('/setup', setupData())->assertRedirect(route('dashboard'));

    expect(User::firstWhere('email', 'owner@example.com')->can('projects.create'))->toBeTrue();
});

it('validates the setup form', function () {
    $this->post('/setup', setupData(['password_confirmation' => 'diversa']))->assertSessionHasErrors('password');
    $this->post('/setup', setupData(['email' => userWithRole('member')->email]))->assertSessionHasErrors('email');

    expect(User::hasOrganizer())->toBeFalse();
});

it('hides the setup once an organizer exists', function () {
    userWithRole('organizer');

    $this->get('/setup')->assertNotFound();
    $this->post('/setup', setupData())->assertNotFound();
    $this->get('/login')->assertOk();
});

it('saves the company email domains chosen at setup, normalized', function () {
    $this->post('/setup', setupData(['email' => 'owner@azienda.it', 'domains' => '@Azienda.it, azienda.com  azienda.it', 'smtp_from_address' => 'noreply@azienda.com']))
        ->assertRedirect(route('dashboard'));

    expect(EmailDomain::orderBy('domain')->pluck('domain')->all())->toBe(['azienda.com', 'azienda.it']);
});

it('rejects setup domains that are missing, invalid or not matching the owner email', function (array $data, string $field) {
    $this->post('/setup', setupData($data))->assertSessionHasErrors($field);

    expect(User::hasOrganizer())->toBeFalse()
        ->and(EmailDomain::exists())->toBeFalse();
})->with([
    'mancanti' => [['domains' => ''], 'domains'],
    'non validi' => [['domains' => 'azienda'], 'domains'],
    'email di un altro dominio' => [['email' => 'owner@gmail.com', 'domains' => 'azienda.it'], 'email'],
    'mittente di un altro dominio' => [['email' => 'owner@azienda.it', 'domains' => 'azienda.it', 'smtp_from_address' => 'noreply@gmail.com'], 'smtp_from_address'],
]);

it('reports an invalid domain only on the domains field, not on the email', function () {
    $this->post('/setup', setupData(['domains' => 'fsafds@example.com']))
        ->assertSessionHasErrors('domains')
        ->assertSessionDoesntHaveErrors(['email', 'smtp_from_address']);
});

it('saves the SMTP server chosen at setup and sends mail through it', function () {
    $this->post('/setup', setupData())->assertRedirect(route('dashboard'));

    $smtp = MailSetting::sole();
    expect($smtp->only(['host', 'port', 'username', 'password', 'from_address']))->toBe([
        'host' => 'smtp.example.com', 'port' => 587, 'username' => 'app', 'password' => 'segreta', 'from_address' => 'noreply@example.com',
    ])->and($smtp->getRawOriginal('password'))->not->toBe('segreta');

    app()->forgetInstance('mail.manager');
    app('mail.manager');
    expect(config('mail.default'))->toBe('smtp')
        ->and(config('mail.mailers.smtp.host'))->toBe('smtp.example.com')
        ->and(config('mail.from.address'))->toBe('noreply@example.com');
});

it('requires the SMTP server at setup', function (string $field) {
    $this->post('/setup', setupData([$field => '']))->assertSessionHasErrors($field);

    expect(User::hasOrganizer())->toBeFalse()
        ->and(MailSetting::exists())->toBeFalse();
})->with(['smtp_host', 'smtp_port', 'smtp_from_address']);
