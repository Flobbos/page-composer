<?php

namespace Flobbos\PageComposer\Livewire;;

use App\Models\User;
use Livewire\Component;
use Flobbos\PageComposer\Models\Bug;
use Flobbos\PageComposer\Models\Comment;
use Flobbos\PageComposer\Notifications\BugResponseNotification;

class CommentComponent extends Component
{
    public $content, $comments;

    public int $bugId;

    public function mount(int $bugId): void
    {
        $this->bugId = $bugId;
    }

    private function getBug(): Bug
    {
        return Bug::with('user', 'comments.user')->findOrFail($this->bugId);
    }

    public function saveComment()
    {
        $bug = $this->getBug();

        $bug->comments()->save(new Comment([
            'user_id' => auth()->id(),
            'content' => $this->content,
        ]));

        if (config('pagecomposer.bug_user') !== auth()->id()) {
            // Notify the user that a response has been made
            if ($bug->user->id != auth()->id()) {
                $bug->user->notify(new BugResponseNotification($bug->id, auth()->user()->name));
            }

            // Notify the responsible person
            if (auth()->id() != config('pagecomposer.bug_user')) {
                User::find(config('pagecomposer.bug_user'))->notify(new BugResponseNotification($bug->id, auth()->user()->name));
            }
        }

        $this->reset('content');
    }

    public function render()
    {
        return view('page-composer::livewire.comment-component', [
            'bug' => $this->getBug(),
        ]);
    }
}
