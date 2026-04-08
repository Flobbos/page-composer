# Release v1.0.0

## Breaking Changes

- **Minimum PHP version**: 8.3+
- **Minimum Laravel version**: 13.x (dropped support for Laravel 10, 11, 12)
- **Minimum Livewire version**: 4.x (dropped support for Livewire 3)
- **Removed external SortableJS dependency**: The `@wotz/livewire-sortablejs` CDN script has been removed. Livewire 4 ships with native `wire:sortable` support, so drag-and-drop works out of the box without any external JavaScript libraries.

## Upgrade Guide

1. Ensure your application runs **Laravel 13** and **Livewire 4**
2. Run `composer update flobbos/page-composer`
3. If you published element stubs or blade views, check for any `wire:model.defer` usage in your custom elements and replace with `wire:model`
4. If you extended any components that used `getSortedRowsProperty()`, `getSortedColumnsProperty()`, or `getSortedElementsProperty()`, use the `#[Computed]` attribute-based `$this->sortedRows`, `$this->sortedColumns`, or `$this->sortedElements` instead

## What's New

### Native Drag & Drop
Removed the external `@wotz/livewire-sortablejs` library in favor of Livewire 4's built-in `wire:sortable` support. This means:
- No more CDN script injection
- Faster page loads
- No third-party dependency for drag-and-drop functionality
- Row reordering (mini map) and column reordering work natively

### Stable Component Keys
Replaced all `uniqid()` usage with stable, deterministic keys for Livewire components. This prevents unnecessary component re-mounting on every render cycle, resulting in better performance and more predictable state management.

### Modern Livewire 4 Patterns
- Replaced all `wire:model.defer` bindings with `wire:model` (deferred by default in Livewire 3+, removed in Livewire 4) across 47 instances in 14 blade files
- Converted legacy `$queryString` property to `#[Url]` attributes in BugComponent
- Removed redundant `get*Property()` accessor methods in favor of `#[Computed]` attributes

## Bug Fixes

- **Fixed namespace syntax errors**: Corrected double-semicolon namespace declarations (`namespace Foo;;`) in 9 component files (ColumnComponent, ImageUploadComponent, ElementList, CommentComponent, MultiSelect, SelectInput, DatePicker, LanguageComponent, CategoryComponent)
- **Fixed memory leak**: Replaced `setInterval` with `setTimeout` for auto-hiding flash messages in page composer and page index views
- **Fixed unreachable code**: Removed dead `return false` statement after an if/else block in `ImageUploadComponent::imageExists()`
- **Fixed typo**: Corrected `$comlumn_key` to `$column_key` in ElementList component

## Code Quality

- Removed commented-out `dd()` debug statement in PageComposer
- Removed large commented-out code block in `loadTemplate()` method
- Removed commented-out model publishing block in ServiceProvider
- Replaced `uniqid()` with `Str::ulid()` for filename generation (more unique, sortable)
- Replaced `uniqid()` with `Str::random(8)` for element IDs

## Files Changed

### PHP Components (14 files)
- `PageComposer.php` — removed debug code, legacy accessor, commented block
- `RowComponent.php` — removed legacy accessor
- `ColumnComponent.php` — fixed namespace, removed legacy accessor, updated callers
- `ImageUploadComponent.php` — fixed namespace, unreachable code, replaced `uniqid()`
- `BugComponent.php` — converted `$queryString` to `#[Url]`, replaced `uniqid()`
- `ElementList.php` — fixed namespace, fixed typo
- `CommentComponent.php` — fixed namespace
- `CategoryComponent.php` — fixed namespace
- `LanguageComponent.php` — fixed namespace
- `DatePicker.php` — fixed namespace
- `MultiSelect.php` — fixed namespace
- `SelectInput.php` — fixed namespace
- `Elements/Photo.php` — replaced `uniqid()`
- `PageComposerServiceProvider.php` — removed commented code

### Blade Views (22 files)
- `page-composer.blade.php` — removed SortableJS CDN, stable keys, `setTimeout` fix, `wire:model` update
- `page-index.blade.php` — stable keys, `setTimeout` fix
- `row-component.blade.php` — `wire:model` update
- `column-component.blade.php` — no changes needed (already clean)
- `multi-select-input.blade.php` — stable loop keys
- `base-element.blade.php` — stable modal ID
- `settings/general.blade.php` — stable component keys
- `settings/media.blade.php` — stable component keys
- 14 element/component blade files — `wire:model.defer` -> `wire:model`

### Config
- `composer.json` — PHP ^8.3, Laravel 13.*, Livewire ^4.0
- `.gitignore` — added `.claude/`
- `README.md` — updated compatibility table and Livewire version reference
