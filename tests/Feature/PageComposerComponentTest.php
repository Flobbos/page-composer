<?php

use Flobbos\PageComposer\Livewire\PageComposer;
use Flobbos\PageComposer\Models\Category;
use Flobbos\PageComposer\Models\ColumnItem;
use Flobbos\PageComposer\Models\Element;
use Flobbos\PageComposer\Models\Page;
use Flobbos\PageComposer\Models\PageTranslation;
use Flobbos\PageComposer\Models\Row;
use Flobbos\PageComposer\Services\PageBuilder;
use Flobbos\PageComposer\Services\PageBuilderResult;
use Illuminate\Support\Collection;
use Livewire\Livewire;

beforeEach(function () {
    $this->languages = seedLanguages(['en', 'de']);
    $this->element = seedElement('Text', 'text');
    $this->category = Category::create([]);
});

/**
 * Build a minimal in-memory state tree that satisfies the validation rules
 * declared in pagecomposer.rules.
 */
function pageState(int $categoryId, int $elementId): array
{
    return [
        'pageData' => [
            'name' => 'Hello',
            'photo' => 'photos/hello.jpg',
            'newsletter_image' => null,
            'slider_image' => null,
            'published_on' => null,
            'category_id' => $categoryId,
        ],
        'pageTranslations' => [
            'en' => [
                'language_id' => 1,
                'content' => ['title' => 'Hello'],
            ],
        ],
        'rows' => [
            'en' => [
                'rows' => [
                    [
                        'uuid' => 'tmp-1',
                        'sorting' => 1,
                        'alignment' => 'center',
                        'expanded' => false,
                        'active' => true,
                        'attributes' => [],
                        'columns' => [
                            [
                                'sorting' => 1,
                                'column_size' => 6,
                                'active' => true,
                                'attributes' => [],
                                'column_items' => [
                                    [
                                        'element_id' => $elementId,
                                        'name' => 'Text',
                                        'component' => 'text',
                                        'icon' => '<svg></svg>',
                                        'sorting' => 1,
                                        'active' => true,
                                        'attributes' => [],
                                        'content' => ['body' => 'Lorem'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];
}

it('mounts with a default page array when no id is provided', function () {
    Livewire::test(PageComposer::class)
        ->assertSet('pageId', null)
        ->assertSet('pageData.name', null)
        ->assertSet('pageData.photo', null);
});

it('hydrates from the DB when mounted with an existing page id', function () {
    $page = new Page();
    $page->name = 'Existing';
    $page->photo = 'photos/existing.jpg';
    $page->category_id = $this->category->id;
    $page->save();
    $page->translations()->create([
        'language_id' => $this->languages->firstWhere('locale', 'en')->id,
        'content' => ['title' => 'Existing'],
        'slug' => 'existing',
    ]);

    Livewire::test(PageComposer::class, ['page' => $page->id])
        ->assertSet('pageId', $page->id)
        ->assertSet('pageData.name', 'Existing')
        ->assertSet('pageData.photo', 'photos/existing.jpg')
        ->assertSet('photo', 'photos/existing.jpg');
});

it('blocks saveContent when required fields are missing', function () {
    Livewire::test(PageComposer::class)
        ->call('saveContent', false)
        ->assertHasErrors(['pageData.name', 'pageData.photo', 'pageData.category_id']);

    expect(Page::count())->toBe(0);
});

it('persists through saveContent on the happy path', function () {
    $state = pageState($this->category->id, $this->element->id);
    $enId = $this->languages->firstWhere('locale', 'en')->id;

    $component = Livewire::test(PageComposer::class)
        ->call('addLanguage', $enId)
        ->dispatch('eventImageUploadComponentSaved.pageComposer.mainPhoto', field: 'photo', imagePath: $state['pageData']['photo'])
        ->set('pageData', $state['pageData'])
        ->set('pageCategory', ['id' => $this->category->id])
        ->set('pageTranslations', $state['pageTranslations'])
        ->set('rows', $state['rows'])
        ->call('saveContent', false)
        ->assertHasNoErrors();

    expect($component->get('exceptionMessage'))->toBeNull();
    expect(Page::count())->toBe(1);
    expect(PageTranslation::count())->toBe(1);
    expect(Row::count())->toBe(1);
    expect(ColumnItem::count())->toBe(1);
});

it('rolls back DB writes and shows a sanitized error when persist throws', function () {
    // Swap PageBuilder for one that always blows up after the page row is
    // created, simulating a mid-transaction failure. Returning rows that
    // would otherwise be valid lets us assert the rollback by counting
    // rows in the DB.
    app()->bind(PageBuilder::class, function () {
        return new class extends PageBuilder {
            public function persist(?int $pageId, array $pageData, array $pageTranslations, array $pageTags, array $rows, Collection $languagesByLocale): PageBuilderResult
            {
                return \Illuminate\Support\Facades\DB::transaction(function () use ($pageData) {
                    $page = new Page();
                    $page->name = $pageData['name'] ?? null;
                    $page->photo = $pageData['photo'] ?? null;
                    $page->category_id = $pageData['category_id'] ?? null;
                    $page->save();

                    throw new \RuntimeException('simulated failure with /etc/passwd path');
                });
            }
        };
    });

    $state = pageState($this->category->id, $this->element->id);
    $rowsBefore = $state['rows'];
    $enId = $this->languages->firstWhere('locale', 'en')->id;

    $component = Livewire::test(PageComposer::class)
        ->call('addLanguage', $enId)
        ->dispatch('eventImageUploadComponentSaved.pageComposer.mainPhoto', field: 'photo', imagePath: $state['pageData']['photo'])
        ->set('pageData', $state['pageData'])
        ->set('pageCategory', ['id' => $this->category->id])
        ->set('pageTranslations', $state['pageTranslations'])
        ->set('rows', $rowsBefore)
        ->call('saveContent', false);

    // Transaction rolled back: no Page row survived.
    expect(Page::count())->toBe(0);

    // In-memory rows still match what we sent in (no IDs leaked from a
    // partial commit, no mutations from the fake builder).
    $component->assertSet('rows', $rowsBefore);
    $component->assertSet('pageId', null);

    // User-facing message is the sanitized constant; the raw exception
    // message (with the path) is not surfaced.
    $component->assertSet('showErrorMessage', true);
    $component->assertSet('exceptionMessage', 'We could not save this page. Please try again.');
});

it('removes a row from state when the deleteRow listener fires', function () {
    $state = pageState($this->category->id, $this->element->id);
    $enId = $this->languages->firstWhere('locale', 'en')->id;

    $component = Livewire::test(PageComposer::class)
        ->call('addLanguage', $enId)
        ->set('rows', $state['rows'])
        ->dispatch('deleteRow', '0');

    expect($component->get('rows.en.rows'))->toHaveCount(0);
});

it('routes the imageSaved listener through the field whitelist', function () {
    $component = Livewire::test(PageComposer::class)
        ->dispatch('eventImageUploadComponentSaved.pageComposer.mainPhoto', field: 'photo', imagePath: 'photos/hi.jpg');

    $component
        ->assertSet('photo', 'photos/hi.jpg')
        ->assertSet('pageData.photo', 'photos/hi.jpg');
});

it('ignores imageSaved events for fields outside the whitelist', function () {
    $component = Livewire::test(PageComposer::class)
        ->dispatch('eventImageUploadComponentSaved.pageComposer.mainPhoto', field: 'arbitrary_property', imagePath: '/etc/passwd');

    // The component should not now have an arbitrary_property attribute set.
    expect($component->get('photo'))->toBeNull();
    expect($component->get('newsletter_image'))->toBeNull();
    expect($component->get('slider_image'))->toBeNull();
});
