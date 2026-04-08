<?php

namespace Flobbos\PageComposer\Livewire;

use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Flobbos\PageComposer\Models\Bug;
use Flobbos\PageComposer\Notifications\BugAddedNotification;
use Flobbos\PageComposer\Notifications\BugReopenedNotification;
use Flobbos\PageComposer\Notifications\BugResolvedNotification;

class BugComponent extends Component
{
    use WithFileUploads;

    #[Url(except: false)]
    public $showTrash = false;

    #[Url(except: false)]
    public $showForm = false;

    #[Url]
    public $bugId;

    public $currentBug;

    public $title, $description, $photos = [], $newPhotos = [];
    public $photoInputKey = 0;
    public $type = 0;

    public $bugs = [];

    public $rules = [
        'title' => 'required',
        'description' => 'required',
        'photos' => 'nullable|array|max:5',
        'photos.*' => 'image|max:2048',
        'type' => 'required',
    ];

    public function mount()
    {
        if ($this->bugId) {
            $this->currentBug = Bug::with('user', 'comments.user')->findOrFail($this->bugId);
        }
    }

    public function closeBug()
    {
        $this->reset();
    }

    public function showBug(Bug $bug)
    {
        $bug->load('user', 'comments.user');
        if (auth()->id() === config('pagecomposer.bug_user') && !$bug->viewed) {
            $bug->viewed = true;
            $bug->save();
        }
        $this->currentBug = $bug;
        $this->bugId = $this->currentBug->id;
    }

    public function hideForm()
    {
        $this->reset('title', 'description', 'photos', 'newPhotos', 'showForm');
        $this->photoInputKey++;
    }

    public function updatedNewPhotos()
    {
        $this->validate([
            'newPhotos' => 'nullable|array',
            'newPhotos.*' => 'image|max:2048',
        ]);

        $this->photos = array_values(array_merge($this->photos, $this->newPhotos));

        if (count($this->photos) > 5) {
            $this->addError('photos', __('You can upload a maximum of 5 screenshots.'));
            $this->photos = array_slice($this->photos, 0, 5);
        }

        $this->newPhotos = [];
        $this->photoInputKey++;
    }

    public function removePhoto(int $index)
    {
        unset($this->photos[$index]);
        $this->photos = array_values($this->photos);
    }

    public function saveBug()
    {
        $this->validate();

        $filenames = [];
        foreach ($this->photos ?? [] as $photo) {
            $filename = auth()->id() . '_' . Str::ulid() . '.' . $photo->getClientOriginalExtension();
            $photo->storeAs('photos', $filename, 'public');
            $filenames[] = $filename;
        }

        $bug = Bug::create([
            'title' => $this->title,
            'description' => $this->description,
            'user_id' => auth()->id(),
            'type' => $this->type,
            // Keep photo for backward compatibility with older code/data.
            'photo' => $filenames[0] ?? null,
            'photos' => $filenames,
        ]);

        if (config('pagecomposer.bug_notifications')) {
            $user = User::find(config('pagecomposer.bug_user'));
            $user->notify(new BugAddedNotification($bug->id, auth()->user()->name));
        }

        session()->flash('message', __('Thank you for your help. Issue created.'));

        $this->reset();
    }

    public function deleteBug(Bug $bug)
    {
        $bug->delete();
    }

    public function restoreBug($id)
    {
        $bug = Bug::withTrashed()->findOrFail($id);
        $bug->restore();
        $bug->save();
    }

    public function resolve(Bug $bug)
    {
        $bug->resolved = !$bug->resolved;
        $bug->save();

        if (config('pagecomposer.bug_notifications')) {
            if ($bug->resolved && auth()->id() != $bug->user_id) {
                $user = User::find($bug->user_id);
                $user->notify(new BugResolvedNotification($bug->title, $bug->id));
            }
            if (!$bug->resolved) {
                $user = User::find(config('pagecomposer.bug_user'));
                $user->notify(new BugReopenedNotification($bug->title, $bug->id));
            }
        }
    }

    public function render()
    {
        if ($this->showTrash) {
            $this->bugs = Bug::with('user')->onlyTrashed()->get();
        } elseif ($this->bugId) {
            $this->currentBug = Bug::with('user')->findOrFail($this->bugId);
        } else {
            $this->bugs = Bug::with('user')->orderBy('created_at', 'desc')->get();
        }
        return view('page-composer::livewire.bug-component');
    }
}
