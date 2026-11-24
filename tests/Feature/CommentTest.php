<?php

use App\Models\Comment;
use App\Models\Task;

it('lets project members and PM comment, not outsiders or the organizer', function () {
    $pm = userWithRole('project_manager');
    $member = userWithRole('member');
    $project = projectOf($pm, $member);
    $task = Task::factory()->for($project)->create();

    $this->actingAs($member)->post("/tasks/{$task->id}/comments", ['text' => 'Fatto metà'])->assertRedirect();
    $this->actingAs($pm)->post("/tasks/{$task->id}/comments", ['text' => 'Ok'])->assertRedirect();
    expect($task->comments()->count())->toBe(2)
        ->and($task->comments()->where('text', 'Fatto metà')->value('created_by'))->toBe($member->id);

    $this->actingAs(userWithRole('member'))->post("/tasks/{$task->id}/comments", ['text' => 'X'])->assertForbidden();
    $this->actingAs(userWithRole('organizer'))->post("/tasks/{$task->id}/comments", ['text' => 'X'])->assertForbidden();

    $this->actingAs($member)->post("/tasks/{$task->id}/comments", ['text' => ''])->assertSessionHasErrors('text');
});

it('lists comments to whoever can see the task', function () {
    $member = userWithRole('member');
    $project = projectOf(userWithRole('project_manager'), $member);
    $comment = Comment::factory()->for(Task::factory()->for($project))->create(['created_by' => $member->id]);

    $this->actingAs(userWithRole('organizer'))->get("/tasks/{$comment->task_id}/comments")->assertOk()->assertSee($comment->text);
    $this->actingAs(userWithRole('member'))->get("/tasks/{$comment->task_id}/comments")->assertForbidden();
});

it('lets the author or the project PM delete a comment', function () {
    $pm = userWithRole('project_manager');
    $author = userWithRole('member');
    $other = userWithRole('member');
    $project = projectOf($pm, $author, $other);
    $task = Task::factory()->for($project)->create();
    [$first, $second] = Comment::factory(2)->for($task)->create(['created_by' => $author->id]);

    $this->actingAs($other)->delete("/comments/{$first->id}")->assertForbidden();

    $this->actingAs($author)->delete("/comments/{$first->id}")->assertRedirect();
    $this->actingAs($pm)->delete("/comments/{$second->id}")->assertRedirect();
    expect(Comment::count())->toBe(0);
});

it('does not let a member removed from the project delete their old comments', function () {
    $member = userWithRole('member');
    $project = projectOf(userWithRole('project_manager'), $member);
    $comment = Comment::factory()->for(Task::factory()->for($project))->create(['created_by' => $member->id]);

    $project->members()->detach($member);

    $this->actingAs($member)->delete("/comments/{$comment->id}")->assertForbidden();
    expect(Comment::find($comment->id))->not->toBeNull();
});
