<?php

namespace Flobbos\PageComposer\Livewire;;

use Flobbos\PageComposer\Models\Bug;
use Livewire\Component;
use Livewire\WithFileUploads;

class BugComponent extends Component
{
    use WithFileUploads;

    public $showTrash = false;
    public $showForm = false;
    public $bugId, $currentBug;

    public $title, $description, $photo;
    public $type = 0;

    public $bugs = [];

    protected $queryString = [
        'showTrash' => ['except' => false],
        'showForm' => ['except' => false],
        'bugId'
    ];

    public $rules = [
        'title' => 'required',
        'description' => 'required',
        'photo' => 'nullable|image|max:1024',
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
        if (auth()->id() === 1 && !$bug->viewed) {
            $bug->viewed = true;
            $bug->save();
        }
        $this->currentBug = $bug;
        $this->bugId = $this->currentBug->id;
    }

    public function hideForm()
    {
        $this->reset('title', 'description', 'showForm');
    }

    public function saveBug()
    {
        $this->validate();

        $filename = null;
        if ($this->photo) {
            $filename = auth()->id() . '_' . uniqid() . '.' . $this->photo->getClientOriginalExtension();
            $this->photo->storeAs('public/photos', $filename);
        }

        Bug::create([
            'title' => $this->title,
            'description' => $this->description,
            'user_id' => auth()->id(),
            'type' => $this->type,
            'photo' => $filename
        ]);

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
