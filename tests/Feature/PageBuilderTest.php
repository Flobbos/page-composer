<?php

use Flobbos\PageComposer\Models\Category;
use Flobbos\PageComposer\Models\Column;
use Flobbos\PageComposer\Models\ColumnItem;
use Flobbos\PageComposer\Models\Page;
use Flobbos\PageComposer\Models\PageTranslation;
use Flobbos\PageComposer\Models\Row;
use Flobbos\PageComposer\Models\Tag;
use Flobbos\PageComposer\Services\PageBuilder;

beforeEach(function () {
    $this->builder = new PageBuilder();
    $this->languages = seedLanguages(['en', 'de'])->keyBy('locale');
    $this->element = seedElement('Text', 'text');
    $this->category = Category::create([]);
});

function buildBaseState(\Flobbos\PageComposer\Models\Element $element, int $categoryId): array
{
    return [
        'pageData' => [
            'name' => 'Hello',
            'photo' => null,
            'newsletter_image' => null,
            'slider_image' => null,
            'published_on' => null,
            'category_id' => $categoryId,
        ],
        'translations' => [
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
                                        'element_id' => $element->id,
                                        'sorting' => 1,
                                        'active' => true,
                                        'attributes' => [],
                                        'content' => ['body' => 'Lorem ipsum'],
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

it('creates a new Page when pageId is null', function () {
    $state = buildBaseState($this->element, $this->category->id);

    $result = $this->builder->persist(
        null,
        $state['pageData'],
        $state['translations'],
        [],
        $state['rows'],
        $this->languages,
    );

    expect($result->page)->toBeInstanceOf(Page::class);
    expect($result->page->id)->not->toBeNull();
    expect($result->page->name)->toBe('Hello');
    expect(Page::count())->toBe(1);
});

it('updates an existing Page when pageId is given', function () {
    $page = new Page();
    $page->name = 'Original';
    $page->category_id = $this->category->id;
    $page->save();
    $state = buildBaseState($this->element, $this->category->id);
    $state['pageData']['name'] = 'Updated';

    $result = $this->builder->persist(
        $page->id,
        $state['pageData'],
        $state['translations'],
        [],
        $state['rows'],
        $this->languages,
    );

    expect($result->page->id)->toBe($page->id);
    expect(Page::count())->toBe(1);
    expect($page->fresh()->name)->toBe('Updated');
});

it('persists translations on insert path', function () {
    $state = buildBaseState($this->element, $this->category->id);

    $result = $this->builder->persist(
        null,
        $state['pageData'],
        $state['translations'],
        [],
        $state['rows'],
        $this->languages,
    );

    expect(PageTranslation::count())->toBe(1);
    $translation = PageTranslation::first();
    expect($translation->page_id)->toBe($result->page->id);
    expect($translation->language_id)->toBe(1);
    expect($translation->slug)->toBe('hello');
});

it('updates an existing translation when its id is supplied', function () {
    $page = new Page();
    $page->name = 'Existing';
    $page->category_id = $this->category->id;
    $page->save();
    $existing = $page->translations()->create([
        'language_id' => 1,
        'content' => ['title' => 'Old'],
        'slug' => 'old',
    ]);

    $state = buildBaseState($this->element, $this->category->id);
    $state['translations']['en']['id'] = $existing->id;
    $state['translations']['en']['content'] = ['title' => 'Brand New'];

    $this->builder->persist(
        $page->id,
        $state['pageData'],
        $state['translations'],
        [],
        $state['rows'],
        $this->languages,
    );

    expect(PageTranslation::count())->toBe(1);
    expect($existing->fresh()->slug)->toBe('brand-new');
});

it('skips translations without a language_id', function () {
    $state = buildBaseState($this->element, $this->category->id);
    $state['translations']['de'] = ['content' => ['title' => 'Hallo']];

    $this->builder->persist(
        null,
        $state['pageData'],
        $state['translations'],
        [],
        $state['rows'],
        $this->languages,
    );

    expect(PageTranslation::count())->toBe(1);
});

it('syncs page tags', function () {
    seedLanguages(['en']);
    $tagA = Tag::create([]);
    $tagB = Tag::create([]);

    $state = buildBaseState($this->element, $this->category->id);

    $result = $this->builder->persist(
        null,
        $state['pageData'],
        $state['translations'],
        [['id' => $tagA->id], ['id' => $tagB->id]],
        $state['rows'],
        $this->languages,
    );

    expect($result->page->tags->pluck('id')->all())->toEqualCanonicalizing([$tagA->id, $tagB->id]);
});

it('persists rows, columns, and items on the insert path', function () {
    $state = buildBaseState($this->element, $this->category->id);

    $this->builder->persist(
        null,
        $state['pageData'],
        $state['translations'],
        [],
        $state['rows'],
        $this->languages,
    );

    expect(Row::count())->toBe(1);
    expect(Column::count())->toBe(1);
    expect(ColumnItem::count())->toBe(1);

    $row = Row::first();
    expect($row->language_id)->toBe(1);
    expect($row->available_space)->toBe(6);
});

it('returns rows with newly assigned IDs filled in', function () {
    $state = buildBaseState($this->element, $this->category->id);

    $result = $this->builder->persist(
        null,
        $state['pageData'],
        $state['translations'],
        [],
        $state['rows'],
        $this->languages,
    );

    $row = $result->rows['en']['rows'][0];
    expect($row['id'])->toBe(Row::first()->id);
    expect($row['columns'][0]['id'])->toBe(Column::first()->id);
    expect($row['columns'][0]['column_items'][0]['id'])->toBe(ColumnItem::first()->id);
});

it('updates existing rows / columns / items when their ids are present', function () {
    $state = buildBaseState($this->element, $this->category->id);

    $first = $this->builder->persist(
        null,
        $state['pageData'],
        $state['translations'],
        [],
        $state['rows'],
        $this->languages,
    );

    // Take the result, mutate the body content, persist again as an update.
    $rows = $first->rows;
    $rows['en']['rows'][0]['columns'][0]['column_items'][0]['content'] = ['body' => 'Edited'];

    $this->builder->persist(
        $first->page->id,
        $state['pageData'],
        $state['translations'],
        [],
        $rows,
        $this->languages,
    );

    expect(Row::count())->toBe(1);
    expect(Column::count())->toBe(1);
    expect(ColumnItem::count())->toBe(1);
    expect(ColumnItem::first()->content)->toBe(['body' => 'Edited']);
});

it('rolls back the entire persist when something inside fails', function () {
    $state = buildBaseState($this->element, $this->category->id);
    // column_items.content is NOT NULL; force the inner insert to fail.
    $state['rows']['en']['rows'][0]['columns'][0]['column_items'][0]['content'] = null;

    $caught = null;
    try {
        $this->builder->persist(
            null,
            $state['pageData'],
            $state['translations'],
            [],
            $state['rows'],
            $this->languages,
        );
    } catch (\Throwable $ex) {
        $caught = $ex;
    }

    expect($caught)->not->toBeNull();
    expect(Page::count())->toBe(0);
    expect(Row::count())->toBe(0);
    expect(Column::count())->toBe(0);
    expect(ColumnItem::count())->toBe(0);
});

it('skips locales that have no matching language', function () {
    $state = buildBaseState($this->element, $this->category->id);
    $state['rows']['fr'] = $state['rows']['en'];

    $this->builder->persist(
        null,
        $state['pageData'],
        $state['translations'],
        [],
        $state['rows'],
        $this->languages,
    );

    expect(Row::count())->toBe(1);
});

it('computes available_space as 12 minus the sum of column sizes', function () {
    $state = buildBaseState($this->element, $this->category->id);
    $state['rows']['en']['rows'][0]['columns'][] = [
        'sorting' => 2,
        'column_size' => 3,
        'active' => true,
        'attributes' => [],
        'column_items' => [],
    ];

    $this->builder->persist(
        null,
        $state['pageData'],
        $state['translations'],
        [],
        $state['rows'],
        $this->languages,
    );

    expect(Row::first()->available_space)->toBe(3);
});

it('does not overwrite another page\'s row when a foreign row id is supplied', function () {
    // Page A owns a row.
    $aState = buildBaseState($this->element, $this->category->id);
    $pageA = $this->builder->persist(null, $aState['pageData'], $aState['translations'], [], $aState['rows'], $this->languages);
    $foreignRowId = Row::sole()->id;

    // Page B is saved with Page A's row id smuggled into its state (as a
    // tampered Livewire payload could do) and a changed alignment.
    $bState = buildBaseState($this->element, $this->category->id);
    $bState['pageData']['name'] = 'Page B';
    $bState['rows']['en']['rows'][0]['id'] = $foreignRowId;
    $bState['rows']['en']['rows'][0]['alignment'] = 'left';
    $pageB = $this->builder->persist(null, $bState['pageData'], $bState['translations'], [], $bState['rows'], $this->languages);

    // Page A's row must be untouched: still owned by A, alignment unchanged.
    $aRow = Row::find($foreignRowId);
    expect($aRow->page_id)->toBe($pageA->page->id);
    expect($aRow->page_id)->not->toBe($pageB->page->id);
    expect($aRow->alignment)->toBe('center');
});

it('does not overwrite another page\'s column item when a foreign item id is supplied', function () {
    $aState = buildBaseState($this->element, $this->category->id);
    $this->builder->persist(null, $aState['pageData'], $aState['translations'], [], $aState['rows'], $this->languages);
    $foreignItemId = ColumnItem::sole()->id;

    $bState = buildBaseState($this->element, $this->category->id);
    $bState['pageData']['name'] = 'Page B';
    $bState['rows']['en']['rows'][0]['columns'][0]['column_items'][0]['id'] = $foreignItemId;
    $bState['rows']['en']['rows'][0]['columns'][0]['column_items'][0]['content'] = ['body' => 'HIJACKED'];
    $this->builder->persist(null, $bState['pageData'], $bState['translations'], [], $bState['rows'], $this->languages);

    expect(ColumnItem::find($foreignItemId)->content)->toBe(['body' => 'Lorem ipsum']);
});

it('does not overwrite another page\'s translation when a foreign translation id is supplied', function () {
    $aState = buildBaseState($this->element, $this->category->id);
    $pageA = $this->builder->persist(null, $aState['pageData'], $aState['translations'], [], $aState['rows'], $this->languages);
    $foreignTransId = PageTranslation::sole()->id;

    $bState = buildBaseState($this->element, $this->category->id);
    $bState['pageData']['name'] = 'Page B';
    $bState['translations']['en']['id'] = $foreignTransId;
    $bState['translations']['en']['content'] = ['title' => 'HIJACKED'];
    $this->builder->persist(null, $bState['pageData'], $bState['translations'], [], $bState['rows'], $this->languages);

    $aTrans = PageTranslation::find($foreignTransId);
    expect($aTrans->page_id)->toBe($pageA->page->id);
    expect($aTrans->content)->toBe(['title' => 'Hello']);
});
