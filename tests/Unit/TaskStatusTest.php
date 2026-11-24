<?php

use App\Enums\TaskStatus;

it('lets only the assignee move work forward, not the PM', function (TaskStatus $from, TaskStatus $to) {
    expect($from->canTransitionTo($to, isPm: false))->toBeTrue()
        ->and($from->canTransitionTo($to, isPm: true))->toBeFalse();
})->with([
    [TaskStatus::Todo, TaskStatus::InProgress],
    [TaskStatus::InProgress, TaskStatus::InReview],
]);

it('lets only the PM complete or send back a task in review', function (TaskStatus $to) {
    expect(TaskStatus::InReview->canTransitionTo($to, isPm: false))->toBeFalse()
        ->and(TaskStatus::InReview->canTransitionTo($to, isPm: true))->toBeTrue();
})->with([TaskStatus::Completed, TaskStatus::InProgress]);

it('rejects every other transition', function (TaskStatus $from, TaskStatus $to) {
    expect($from->canTransitionTo($to, isPm: true))->toBeFalse();
})->with([
    [TaskStatus::Todo, TaskStatus::InReview],
    [TaskStatus::Todo, TaskStatus::Completed],
    [TaskStatus::InProgress, TaskStatus::Todo],
    [TaskStatus::InProgress, TaskStatus::Completed],
    [TaskStatus::Completed, TaskStatus::InProgress],
    [TaskStatus::Completed, TaskStatus::Todo],
    [TaskStatus::Todo, TaskStatus::Todo],
]);
