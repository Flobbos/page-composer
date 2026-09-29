<?php

use Flobbos\PageComposer\Livewire\BugComponent;
use Flobbos\PageComposer\Livewire\CommentComponent;
use Flobbos\PageComposer\Models\Bug;
use Flobbos\PageComposer\Notifications\BugAddedNotification;
use Flobbos\PageComposer\Notifications\BugResponseNotification;
use Flobbos\PageComposer\Tests\Fixtures\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

beforeEach(function () {
    Schema::create('users', function ($table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    config(['pagecomposer.bug_notifications' => true]);
    Notification::fake();

    $this->reporter = User::create(['name' => 'Reporter']);
});

it('resolves bug users through the configured auth model', function () {
    $bug = Bug::create(['title' => 't', 'description' => 'd', 'type' => 0, 'user_id' => $this->reporter->id]);

    expect($bug->user)->toBeInstanceOf(User::class);
});

it('notifies the configured bug user when a report comes in', function () {
    $owner = User::create(['name' => 'Owner']);
    config(['pagecomposer.bug_user' => $owner->id]);

    Livewire::actingAs($this->reporter)->test(BugComponent::class)
        ->set('title', 'Broken')
        ->set('description', 'It broke')
        ->call('saveBug')
        ->assertHasNoErrors();

    Notification::assertSentTo($owner, BugAddedNotification::class);
});

it('does not crash when the configured bug user does not exist', function () {
    config(['pagecomposer.bug_user' => 999]);

    Livewire::actingAs($this->reporter)->test(BugComponent::class)
        ->set('title', 'Broken')
        ->set('description', 'It broke')
        ->call('saveBug')
        ->assertHasNoErrors();

    expect(Bug::count())->toBe(1);
    Notification::assertNothingSent();
});

it('does not crash commenting when the configured bug user does not exist', function () {
    config(['pagecomposer.bug_user' => 999]);
    $commenter = User::create(['name' => 'Commenter']);
    $bug = Bug::create(['title' => 't', 'description' => 'd', 'type' => 0, 'user_id' => $this->reporter->id]);

    Livewire::actingAs($commenter)->test(CommentComponent::class, ['bugId' => $bug->id])
        ->set('content', 'Looking into it')
        ->call('saveComment')
        ->assertHasNoErrors();

    Notification::assertSentTo($this->reporter, BugResponseNotification::class);
});
