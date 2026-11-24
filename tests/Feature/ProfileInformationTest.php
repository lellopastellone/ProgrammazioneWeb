<?php

use App\Models\Task;
use App\Notifications\EmailChangedNotification;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use App\Livewire\UpdateProfileInformationForm;
use Livewire\Livewire;

test('current profile information is available', function () {
    $this->actingAs($user = User::factory()->create());

    $component = Livewire::test(UpdateProfileInformationForm::class);

    expect($component->state['name'])->toEqual($user->name);
    expect($component->state['email'])->toEqual($user->email);
});

test('profile information can be updated', function () {
    $this->actingAs($user = User::factory()->create());

    Livewire::test(UpdateProfileInformationForm::class)
        ->set('state', ['name' => 'Test', 'surname' => 'Name', 'email' => 'test@example.com', 'current_password' => 'password'])
        ->call('updateProfileInformation');

    expect($user->fresh())
        ->name->toEqual('Test')
        ->surname->toEqual('Name')
        ->email->toEqual('test@example.com');
});

test('changing the email requires the current password', function (string $password) {
    $this->actingAs($user = User::factory()->create());
    $email = $user->email;

    Livewire::test(UpdateProfileInformationForm::class)
        ->set('state', ['name' => $user->name, 'surname' => $user->surname, 'email' => 'altro@example.com', 'current_password' => $password])
        ->call('updateProfileInformation')
        ->assertHasErrors(['current_password']);

    expect($user->fresh()->email)->toBe($email);
})->with(['', 'sbagliata']);

test('the name changes without the current password, which never stays in the component state', function () {
    $this->actingAs($user = User::factory()->create());

    $component = Livewire::test(UpdateProfileInformationForm::class)
        ->set('state', ['name' => 'Nuovo', 'surname' => $user->surname, 'email' => $user->email])
        ->call('updateProfileInformation')
        ->assertHasNoErrors();
    expect($user->fresh()->name)->toBe('Nuovo');

    $component->set('state.current_password', 'password')->call('updateProfileInformation');
    expect($component->state)->not->toHaveKey('current_password');
});

test('a changed email is notified to the old address', function () {
    Notification::fake();
    $this->actingAs($user = User::factory()->create());
    $old = $user->email;

    Livewire::test(UpdateProfileInformationForm::class)
        ->set('state', ['name' => $user->name, 'surname' => $user->surname, 'email' => 'nuova@example.com', 'current_password' => 'password'])
        ->call('updateProfileInformation');

    Notification::assertSentOnDemand(EmailChangedNotification::class, fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === $old);
});

function fakeImage(string $name = 'avatar.png', int $sizeInKb = 1): UploadedFile
{
    // Minimal 1x1 valid PNG binary
    $pngContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
    if ($sizeInKb > 1) {
        $pngContent .= str_repeat('0', ($sizeInKb - 1) * 1024);
    }
    return UploadedFile::fake()->createWithContent($name, $pngContent);
}

test('profile photo can be uploaded', function () {
    Storage::fake('public');

    $this->actingAs($user = User::factory()->create());

    $file = fakeImage('avatar.png');

    Livewire::test(UpdateProfileInformationForm::class)
        ->set('photo', $file)
        ->call('updateProfileInformation')
        ->assertSessionHas('profile_updated', 'Informazioni del profilo modificate correttamente.');

    $user->refresh();

    expect($user->profile_photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->profile_photo_path);
});

test('profile photo cannot exceed 5mb', function () {
    Storage::fake('public');

    $this->actingAs($user = User::factory()->create());

    // 5121 KB supera il limite di 5 MB (5120 KB)
    $file = fakeImage('large-avatar.png', 5121);

    Livewire::test(UpdateProfileInformationForm::class)
        ->set('photo', $file)
        ->call('updateProfileInformation')
        ->assertHasErrors(['photo']);

    expect($user->fresh()->profile_photo_path)->toBeNull();
});

test('profile photo must be an image', function () {
    Storage::fake('public');

    $this->actingAs($user = User::factory()->create());

    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    Livewire::test(UpdateProfileInformationForm::class)
        ->set('photo', $file)
        ->call('updateProfileInformation')
        ->assertHasErrors(['photo']);

    expect($user->fresh()->profile_photo_path)->toBeNull();
});

test('profile photo can be deleted', function () {
    Storage::fake('public');

    $this->actingAs($user = User::factory()->create());

    $file = fakeImage('avatar.png');
    $user->updateProfilePhoto($file);

    expect($user->fresh()->profile_photo_path)->not->toBeNull();

    Livewire::test(UpdateProfileInformationForm::class)
        ->call('deleteProfilePhoto')
        ->assertRedirect(route('profile.show'))
        ->assertSessionHas('profile_updated', 'Informazioni del profilo modificate correttamente.');

    expect($user->fresh()->profile_photo_path)->toBeNull();
});

test('avatar component renders image when profile photo is set and initials when absent', function () {
    Storage::fake('public');

    $userWithoutPhoto = User::factory()->create(['name' => 'Mario', 'surname' => 'Rossi', 'profile_photo_path' => null]);
    $viewWithoutPhoto = $this->blade('<x-avatar :user="$user" />', ['user' => $userWithoutPhoto]);
    $viewWithoutPhoto->assertSee('MR');
    $viewWithoutPhoto->assertDontSee('<img', false);

    $userWithPhoto = User::factory()->create(['name' => 'Luigi', 'surname' => 'Verdi']);
    $userWithPhoto->updateProfilePhoto(fakeImage('avatar.png'));
    $viewWithPhoto = $this->blade('<x-avatar :user="$user" />', ['user' => $userWithPhoto]);
    $viewWithPhoto->assertSee('<img', false);
    $viewWithPhoto->assertSee('Luigi Verdi');
});

test('non-organizer user can see profile photos in users list', function () {
    Storage::fake('public');

    $pm = userWithRole('project_manager');
    $organizer = User::role('organizer')->first();
    $organizer->updateProfilePhoto(fakeImage('organizer.png'));

    $response = $this->actingAs($pm)->get(route('users.index'));
    $response->assertOk();
    $response->assertSee($organizer->profile_photo_url);
});

test('when a member uploads a profile photo other users see it in tasks and dashboard', function () {
    Storage::fake('public');

    $organizer = User::role('organizer')->first();
    $pm = userWithRole('project_manager');
    $member = userWithRole('member');
    $otherMember = userWithRole('member');

    // Il member carica la propria foto profilo
    $member->updateProfilePhoto(fakeImage('member-photo.png'));
    $photoUrl = $member->profile_photo_url;

    $project = projectOf($pm, $member, $otherMember);
    $task = Task::factory()->for($project)->create(['end_date' => now()->addDays(5)]);
    $task->assignees()->attach($member);

    // 1. Il PM vede la foto del member nel tab dei task del progetto
    $this->actingAs($pm)
        ->get(route('projects.tasks.index', $project))
        ->assertOk()
        ->assertSee($photoUrl);

    // 2. L'organizer vede la foto del member nella dashboard (tabella carico del team)
    $this->actingAs($organizer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee($photoUrl);

    // 3. L'organizer vede la foto del member nella lista utenti
    $this->actingAs($organizer)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee($photoUrl);

    // 4. Il PM vede la foto del member nella lista utenti
    $this->actingAs($pm)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee($photoUrl);
});

test('when a project manager uploads a profile photo other users see it', function () {
    Storage::fake('public');

    $organizer = User::role('organizer')->first();
    $pm = userWithRole('project_manager');
    $member = userWithRole('member');

    // Il PM carica la propria foto profilo
    $pm->updateProfilePhoto(fakeImage('pm-photo.png'));
    $photoUrl = $pm->profile_photo_url;

    $project = projectOf($pm, $member);

    // 1. Il Member vede la foto del PM nella pagina del progetto
    $this->actingAs($member)
        ->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee($photoUrl);

    // 2. L'Organizer vede la foto del PM nella propria dashboard (progetti attivi)
    $this->actingAs($organizer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee($photoUrl);

    // 3. L'Organizer vede la foto del PM nella lista utenti
    $this->actingAs($organizer)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee($photoUrl);

    // 4. Un altro PM vede la foto del PM nella lista utenti
    $otherPm = userWithRole('project_manager');
    $this->actingAs($otherPm)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee($photoUrl);
});



