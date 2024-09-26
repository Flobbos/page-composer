<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\PageTemplate;

class TemplateComponent extends Component
{
    public $showTemplateWindow = false;

    public function render()
    {
        return view('page-composer::livewire.template-component')->with([
            'templates' => PageTemplate::select(['id', 'name', 'user_id'])->with('user')->get()
        ]);
    }

    public function deleteTemplate(PageTemplate $pageTemplate)
    {
        $pageTemplate->delete();
    }
}
