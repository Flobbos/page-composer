<?php

namespace Flobbos\PageComposer\Livewire;;

use Flobbos\PageComposer\Models\Bug;
use Flobbos\PageComposer\Models\Comment;
use Livewire\Component;

class CommentComponent extends Component
{
    public $content, $comments;

    public Bug $bug;

    public function saveComment()
    {
        $this->bug->comments()->save(new Comment([
            'user_id' => auth()->id(),
            'content' => $this->content,
        ]));
        $this->bug->refresh();
        $this->reset('content');
    }

    public function render()
    {
        return view('page-composer::livewire.comment-component');
    }
}
