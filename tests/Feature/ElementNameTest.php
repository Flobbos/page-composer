<?php

use Flobbos\PageComposer\Livewire\ElementComponent;
use Flobbos\PageComposer\Models\Element;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

it('refuses to scaffold an element whose name is not a plain identifier', function (string $name) {
    config(['pagecomposer.allow_web_scaffolding' => true]);

    Livewire::test(ElementComponent::class)
        ->set('name', $name)
        ->set('icon', 'x')
        ->set('createFromTemplate', true)
        ->call('saveElement')
        ->assertHasErrors('name');

    expect(Element::count())->toBe(0);
})->with([
    "X{}system('id');?>",
    '../../../public/Shell',
    '1Starts With A Digit',
]);

it('refuses to register an existing component with a path-like name', function () {
    Livewire::test(ElementComponent::class)
        ->set('name', 'Anything')
        ->set('icon', 'x')
        ->set('createFromTemplate', false)
        ->set('componentName', '../../secrets')
        ->call('saveElement')
        ->assertHasErrors('componentName');
});

it('makes the element command reject unsafe names', function () {
    $this->artisan('page-composer:element', ['name' => "X{}system('id');?>"])
        ->assertFailed();
});

it('scaffolds an element from the package stubs', function () {
    $files = [
        app_path('Livewire/PageComposerElements/QuoteBlock.php'),
        resource_path('views/livewire/page-composer-elements/quote-block.blade.php'),
        resource_path('views/components/page-composer-elements/quote-block.blade.php'),
    ];

    try {
        $this->artisan('page-composer:element', ['name' => 'QuoteBlock'])->assertSuccessful();

        foreach ($files as $file) {
            expect(File::exists($file))->toBeTrue();
        }

        expect(File::get($files[0]))->toContain('class QuoteBlock extends Component');
    } finally {
        File::delete($files);
    }
});

it('does not scaffold files from the web by default', function () {
    Livewire::test(ElementComponent::class)
        ->assertSet('createFromTemplate', false)
        ->set('name', 'Quote Block')
        ->set('icon', '<svg></svg>')
        ->set('createFromTemplate', true)
        ->call('saveElement')
        ->assertHasErrors('componentName');

    expect(Element::count())->toBe(0)
        ->and(File::exists(app_path('Livewire/PageComposerElements/QuoteBlock.php')))->toBeFalse();
});

it('keeps the component name and files when editing an element with scaffolding off', function () {
    $element = seedElement('Text', 'text');

    Livewire::test(ElementComponent::class)
        ->call('editElement', $element->id)
        ->set('name', 'Rich Text')
        ->call('updateElement', $element->id)
        ->assertHasNoErrors();

    expect($element->fresh()->name)->toBe('Rich Text')
        ->and($element->fresh()->component)->toBe('text');
});
