<x-page-composer::base-element :data="$data" :item-key="$itemKey" :showElementInputs="$showElementInputs" :sorting="$sorting" :previewMode="$previewMode" :hasContent="$this->hasContent()">

    <div class="flex flex-col">
        <div x-data="pageComposerEditor({})">
            <input type="hidden" x-ref="input" wire:model="data.content.text">

            <div wire:ignore>
                <div x-ref="editor">{!! $data['content']['text'] ?? null !!}</div>
            </div>
        </div>
    </div>

</x-page-composer::base-element>
