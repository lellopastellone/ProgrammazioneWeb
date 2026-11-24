<?php

use App\Models\Task;
use App\View\Components\Calendar;
use Carbon\CarbonImmutable;

function taskBetween(?string $start, ?string $end): Task
{
    return (new Task)->forceFill(['start_date' => $start, 'end_date' => $end]);
}

// Aprile 2026: la prima settimana va da lunedì 30 marzo a domenica 5 aprile.
$april = CarbonImmutable::create(2026, 4, 1);

it('builds monday-to-sunday weeks covering the month', function () use ($april) {
    $weeks = Calendar::monthWeeks($april);

    expect($weeks)->toHaveCount(5)
        ->and($weeks[0][0]->toDateString())->toBe('2026-03-30')
        ->and($weeks[4][6]->toDateString())->toBe('2026-05-03');
});

it('puts each task on its end date, using the other date when one is missing', function () use ($april) {
    $long = taskBetween('2026-04-03', '2026-04-07');
    $dueOnly = taskBetween(null, '2026-04-07');
    $startOnly = taskBetween('2026-04-09', null);
    $undated = taskBetween(null, null);

    $calendar = new Calendar($april, [$startOnly, $long, $dueOnly, $undated]);

    expect($calendar->dueOn(CarbonImmutable::parse('2026-04-07'))->all())->toBe([$long, $dueOnly])
        ->and($calendar->dueOn(CarbonImmutable::parse('2026-04-03'))->all())->toBe([]) // inizio: nessun puntino
        ->and($calendar->counts['2026-04-05'])->toBe(0) // a metà durata: nessun puntino
        ->and($calendar->counts['2026-04-09'])->toBe(1)
        ->and($calendar->entries)->toHaveCount(3);
});

it('assigns distinct colors from SPRINT_PALETTE to multiple sprints and respects pre-assigned colors', function () use ($april) {
    $sprint1 = (new \App\Models\Iteration)->forceFill(['starts_on' => '2026-04-01', 'ends_on' => '2026-04-07']);
    $sprint2 = (new \App\Models\Iteration)->forceFill(['starts_on' => '2026-04-08', 'ends_on' => '2026-04-14']);
    $sprint3 = (new \App\Models\Iteration)->forceFill(['starts_on' => '2026-04-15', 'ends_on' => '2026-04-21']);

    $calendar = new Calendar($april, [], false, [$sprint1, $sprint2, $sprint3]);

    // Quando non pre-assegnati, ruotano sulla palette (accent, info, warning)
    expect($calendar->sprintOn(CarbonImmutable::parse('2026-04-02'))[1])->toBe('accent')
        ->and($calendar->sprintOn(CarbonImmutable::parse('2026-04-10'))[1])->toBe('info')
        ->and($calendar->sprintOn(CarbonImmutable::parse('2026-04-18'))[1])->toBe('warning');

    // Quando hanno un colore pre-assegnato (es. ordine globale di progetto), viene preservato
    $sprintCrossMonth = (new \App\Models\Iteration)->forceFill(['starts_on' => '2026-04-25', 'ends_on' => '2026-05-05']);
    $sprintCrossMonth->color = 'warning';

    $calendarApril = new Calendar($april, [], false, [$sprintCrossMonth]);
    $calendarMay = new Calendar(CarbonImmutable::create(2026, 5, 1), [], false, [$sprintCrossMonth]);

    expect($calendarApril->sprintOn(CarbonImmutable::parse('2026-04-28'))[1])->toBe('warning')
        ->and($calendarMay->sprintOn(CarbonImmutable::parse('2026-05-02'))[1])->toBe('warning');
});

