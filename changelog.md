## Version History

### v. 2.0.0

A structural rewrite of the `PageComposer` Livewire component. Same Laravel 13 / Livewire 4 / PHP 8.3 baseline as 1.x, no schema changes. See the `Upgrading from 1.x to 2.x` section in the README for migration steps.

#### Feature: deferred structural deletes

Row / column / element removal is now staged in the editor state and only persisted to the database when the page is saved. A page refresh before save restores the removed content. On save, `PageBuilder::persist` scans for orphans (rows / columns / column items present in the DB but absent from the in-memory state tree) and deletes them inside the same transaction as the upsert. Confirm-dialog copy in the row and column views now reads "Applied when you save the page."

#### Breaking

- **Validation rule keys** moved from `page.*` to `pageData.*`. The form-state property was renamed from `$page` (untyped, colliding with mount's `{page}` route param) to `?array $pageData = null`. Published configs need updating; see the upgrade guide.
- **Row / column parent-child sync** now uses Livewire 4's `#[Modelable]` instead of a three-hop dispatch chain. `RowComponent::$row` and `ColumnComponent::$column` are bound via `wire:model="..."` from their parents. Removed: `itemsUpdated.{target}` / `columnUpdated` / `rowUpdated` events, `RowComponent::$source`, `ColumnComponent::$target`, and the matching listeners.
- **Image upload listener routing** now reads a `field` payload from `ImageUploadComponent`. Any view mounting an upload component for the page composer must pass `fieldName="..."` (allowed: `photo`, `newsletter_image`, `slider_image`). Six copy-paste listener methods on the orchestrator collapsed to two stacked-attribute methods + a whitelisted `setPhotoField` helper.
- **Server-controlled props are `#[Locked]`** (frontend can no longer write to them via wire:model): `$elements`, `$pageId`, `$exceptionMessage`, `$showErrorMessage`, `$categories`, `$tags`, `$photo`, `$newsletter_image`, `$slider_image`, `$languages`, `$currentLanguage`, `$availableLanguages`, `$selectableLanguages`.
- **Sanitized error messages.** Save / update no longer flash `$ex->getMessage() . ' ' . $ex->getLine() . ' ' . $ex->getFile()` to the user (filesystem path leak); they `report($ex)` and surface a generic message instead.
- **Configurable date format.** `pagecomposer.date_format` controls the date picker's display/parse format (default `'m-d-Y'` for parity; `'Y-m-d'` recommended for new installs).
- **Pest plugin dependencies bumped to `^4.0`** (`pestphp/pest-plugin-laravel`, `pestphp/pest-plugin-livewire`); both have Laravel 13 / Livewire 4 compatible 4.x releases.

#### Architecture

- New service classes under `Flobbos\PageComposer\Services\`:
    - `PageBuilder` + `PageBuilderResult`: transactional upsert of Page + tags + translations + rows + columns + items. Replaces the duplicated `saveContent` / `updateContent` triple-nested foreach loops.
    - `PageComposerCache`: one entry point for the four cached lookup tables (elements, languages, categories, tags) with `forgetAll()`.
    - `SortService`: shared row/column reorder algorithm.
- `PageComposer` Livewire component split into four traits under `Flobbos\PageComposer\Livewire\Concerns\`: `HandlesImageUploads`, `HandlesTemplates`, `InteractsWithLanguages`, `ManagesRows`. Component file dropped from 902 to ~330 lines.
- `PageComposerServiceProvider` Livewire registration is now driven by a class-list constant + a `Str::kebab(class_basename(...))` loop, replacing 17 individual `Livewire::component(...)` calls.

#### Robustness

- `saveContent` and `updateContent` (now thin wrappers around `PageBuilder::persist`) are wrapped in `DB::transaction`. A mid-save exception no longer leaves orphan rows / columns / column items.
- Languages are pre-loaded once per save instead of issued per-translation and per-row via `Language::where('locale', $key)->first()`.
- `ElementComponent::render()` reads from `PageComposerCache` instead of issuing `Element::all()` on every render. `saveElement` and `updateElement` bust the cache after writing.

#### Fixes (pre-release integration testing)

- **Creating a page is no longer blocked by a stray translation entry.** The meta/translation settings partial rendered before a language was selected, binding its inputs to an empty locale (`pageTranslations..content.title`). A title typed there landed in a locale-less `pageTranslations.content` bucket that failed the `pageTranslations.*.content.title` rule and blocked every save. The partial is now gated behind a selected language, matching its already-gated toolbar button.
- **`PageBuilder` scopes every record lookup to the owning page.** Row / column / column-item / translation updates resolved client-supplied IDs with an unscoped `Model::find()`, so a stale or tampered editor payload could update — or re-parent — another page's records. Lookups now go through the owning relationship (`$page->rows()`, `$rowModel->columns()`, `$columnModel->column_items()`, `$page->translations()`), ownership keys (`id` / `page_id` / `language_id` / `row_id` / `column_id`) are stripped from update payloads, and an unmatched ID falls back to an insert under the correct parent.
- **The language picker keeps its full list after a selection.** `hydrateLanguages()` assigned the cached language collection to `$selectableLanguages` and then `forget()`-ed from it, mutating the shared instance so selected languages disappeared from the master `$languages` list. Both lists are now derived with non-mutating `whereIn` / `whereNotIn`.

#### Tests

- New Pest 4 suite: 35 tests, 94 assertions, in-memory sqlite via Orchestra Testbench.
    - `tests/Unit/SortServiceTest.php` (8) — pure-function reorder coverage.
    - `tests/Feature/PageComposerCacheTest.php` (7) — cache hit/refresh/forget across all four lookups.
    - `tests/Feature/PageBuilderTest.php` (15) — create/update Page, translation upsert, tag sync, row/column/item persistence, transaction rollback, locale skipping, available_space recompute, cross-page ownership scoping (foreign row/item/translation IDs).
    - `tests/Feature/PageComposerComponentTest.php` (10) — component-level: mount default + hydrate, validation blocks, happy-path save, rollback safety with sanitized error, deleteRow listener, imageSaved listener whitelist, create-flow translation binding gated on a selected language.
    - `tests/Feature/InteractsWithLanguagesTest.php` (1) — master language list stays intact after a language is selected.
- `tests/Fixtures/StubElement.php` registered under `page-composer-elements.{text,photo,youtube}` so the orchestrator's view renders without the host-app element classes being present.

### v. 1.0.3

- **Feature**: Expanded the default Quill toolbar. In addition to the Normal / H1–H3 dropdown, it now includes bold / italic / underline, ordered + bullet lists, link, and clear-formatting.

### v. 1.0.2

- **Breaking (drag & drop)**: Migrated from `wire:sortable` (removed external `@wotz/livewire-sortablejs` library) to Livewire 4's native `wire:sort` directive. Sort handler callbacks now receive `($id, $position)` instead of an array of items.
- **Breaking (Quill Alpine component)**: Renamed the package's Alpine component from `quillEditor` to `pageComposerEditor` to avoid collisions with host apps that register their own `quillEditor`. Published copies of the `text` and `headline-text` element views must be updated to use `x-data="pageComposerEditor({})"`.
- **Feature**: Quill editor toolbar is now configurable via the `quill_toolbar` config key.
- **Fix**: Caching of lookup tables (languages, elements, categories, tags) now stores arrays (`->toArray()`) instead of Eloquent Collections, and rehydrates fresh model instances on read. Prevents `__PHP_Incomplete_Class` unserialize errors when the cache spans framework/driver changes.
- **Fix**: Blade parse error in `page-composer.blade.php` caused by nested inline array default inside `@json(config(...))`.
- **Fix**: Service provider `mergeConfigFrom` now uses the `pagecomposer` key (matching filename and all runtime lookups) instead of the dashed `page-composer`. Unpublished installs will now pick up the package's config defaults properly.

### v. 1.0.1

- **Fix**: Removed hardcoded `version` field from composer.json that caused Packagist rejection on v1.0.0.

### v. 1.0.0

- **Breaking**: Minimum requirements raised to PHP 8.3+, Laravel 13, and Livewire 4
- **Removed**: External `@wotz/livewire-sortablejs` CDN dependency (see 1.0.2 for the correct Livewire 4 replacement)
- **Stable Component Keys**: Replaced all `uniqid()` usage with deterministic keys, preventing unnecessary component re-mounts on every render
- **Livewire 4 Modernization**:
    - Replaced 47 instances of deprecated `wire:model.defer` with `wire:model` across 14 blade files
    - Converted legacy `$queryString` property to `#[Url]` attributes in BugComponent
    - Removed redundant `get*Property()` accessors in favor of `#[Computed]` attributes
- **Fixed**: Double-semicolon namespace declarations in 9 component files
- **Fixed**: `setInterval` memory leak in flash message auto-hide (now uses `setTimeout`)
- **Fixed**: Unreachable `return false` in `ImageUploadComponent::imageExists()`
- **Fixed**: `$comlumn_key` typo in ElementList
- **Cleanup**: Removed commented-out debug code, dead code blocks, and stale service provider entries
- **Cleanup**: Replaced `uniqid()` with `Str::ulid()` for filenames and `Str::random(8)` for element IDs

### v. 0.1.0

- **Livewire 3 Compatibility**: Removed legacy model binding usage across editor flows
    - Replaced public model properties in key components with scalar IDs/arrays where needed
    - Updated media settings bindings to use scalar props instead of direct model access
    - Improved compatibility with Livewire 3/4 hydration and event flows
- **Row Editor Stability**: Fixed row deletion rendering mismatch in page composer
    - Prevented key collisions after row deletion by preserving stable row keys
    - Added explicit wrapper keys in row rendering to ensure correct DOM diffing
- **Layout Composer Enhancements**: Improved column preset and preview behavior
    - Added configurable column preset options
    - Updated segment/preview display behavior for clearer column layout feedback
- **Mini Map Improvements**: Refined sorting and compact display behavior
    - Improved mini map ordering consistency
    - Tuned compact spacing and readability in dense page structures

### v. 0.0.20

- **Mini Map UI Tuning**: Refined mini map row presentation for dense content pages
    - Reduced row/card spacing to improve readability with many rows
    - Kept drag handle placement reliable within row boundaries
    - Tightened per-column preview details to reduce visual noise

### v. 0.0.19

- **Element Generator Fix**: `page-composer:element` now also creates the preview Blade component
    - Added preview stub generation alongside class and Livewire view generation
    - Preview files are now created in `resources/views/components/page-composer-elements/`
- **Element Registration Validation**: Manual element registration now validates preview file presence
    - Validation now checks class file, Livewire view file, and preview Blade file before saving
    - Added clear validation error message when preview file is missing
- **Path Alignment**: Unified preview component paths across creation and validation flows
    - Generator output path and admin validation path now point to the same directory

### v. 0.0.18

- **Element Component Registration**: Enhanced manual component registration workflow
    - Added "Component Name" input field when skipping template generation
    - Users can now provide custom component directory name for existing custom components
    - Added server-side file path validation to ensure both class and view files exist
    - Clear error messages showing expected file paths when validation fails
- **Search Performance**: Improved search efficiency
    - Implemented minimum 4-character threshold via `updatedSearch()` lifecycle hook
    - Searches with 1-3 characters are now skipped (no database query triggered)
    - Clearing search (empty string) still shows full list
    - Reduces query churn during user typing
- **User Confirmations**: Added protection against accidental destructive actions
    - Added confirmation prompt before deleting bug reports
    - Added confirmation prompt before deleting templates
    - Page deletion already had confirmation modals in place
- **Fixed**: Cleaned up duplicate "Version History" header in changelog

### v. 0.0.17

- Added 6 new out-of-the-box elements:
- Hero/Banner (full-width background + overlay text + CTA)
- Grid/Cards (services/features grid with icons/images)
- Bullet List/Features (icon-based feature list)
- Testimonials/Trust Badges (logos, badges, stats)
- Accordion/FAQ (collapsible Q&A sections)
- Call-to-Action Section (centered CTA block)
- Added pagination to PageIndex component (15 items per page with WithPagination trait)
- Added search functionality to PageIndex (searches ID, slug, name, title with live debounced search)
- **Bug Tracker Enhancement**: Added support for multiple screenshots per bug report
    - Users can now upload up to 5 screenshots (2MB each) when creating/viewing bug reports
    - Multiple uploads supported across repeated file picker selections
    - Preview tiles with remove buttons before save
    - Maintains backward compatibility with existing single-photo bug entries
    - Added JSON `photos` column to store multiple filenames
- **Element Management**: Made template generation optional during element creation
    - Added "Create element from template?" checkbox to element creation form
    - Artisan command only runs if checkbox is checked
    - Users can register elements without auto-generating component/view files
- **Element Renaming**: Fixed component file renaming
    - Replaced non-existent `livewire:move` command with PHP `rename()` function
    - Properly renames both class file and blade view file when updating element name
- **Filter Reset**: Fixed filter reset functionality on PageIndex
    - Changed from manual null assignment to using Livewire's `reset()` method
    - Properly clears URL parameter from query string
    - Added proper prop passing to filter component (Blade component)
- **Search Improvements**: Added `trim()` to search input
    - Search now works correctly with leading/trailing spaces
    - Prevents empty-space searches from matching unintended results
- **Fixed**: Pagination serialization error - removed public `$pages` property, paginator now only returned from render()
- **Fixed**: Element data persistence - ensured all element components have `$target` property and dispatch `elementUpdated` events for parent synchronization
- **Fixed**: "pagebuilder:element" command name reference to actual "page-composer:element" in ElementComponent
- **Performance**: Added `#[Computed]` attributes to `sortedElements()`, `sortedColumns()`, and `sortedRows()` for query caching
- **Performance**: Replaced `uniqid()` with stable component keys (based on source/locale/position) to enable Livewire component diffing instead of full re-renders
- Updated element generator stub to include `$target` property and event dispatch pattern

### v. 0.0.16

- added ID field to rows

### v. 0.0.15

- Fixed visual problem in the pages index
- Fixed wrong redirect

### v. 0.0.14

- Fixed a weird entangle issue
- Fixed language component pop-under bug
- Updated Readme with additional information

### v. 0.0.13

- previous changes on github
- fixed an issue with doubled rows on update
