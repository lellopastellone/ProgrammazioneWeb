<?php

use App\Models\Team;

it('rejects input with invalid UTF-8 or NUL bytes', function () {
    $organizer = userWithRole('organizer');

    $this->actingAs($organizer)->get('/users?q=%E9')->assertBadRequest();
    $this->actingAs($organizer)->post('/teams', ['name' => "Backend\0x"])->assertBadRequest();

    expect(Team::exists())->toBeFalse();
});
