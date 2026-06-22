# Release v1.0.2

1.0.0 shipped broken (a stale `version` field in `composer.json` caused Packagist to reject it — that was fixed in 1.0.1). **1.0.2 is the first functional 1.x release** with all the Livewire 4 migration work. Anyone on 1.0.0 or 1.0.1 should upgrade.

Treat this as the real 1.x release notes.

## Platform Requirements

- PHP 8.3+
- Laravel 13.x
- Livewire 4.x

## Breaking Changes

### Drag & drop migrated to Livewire 4's `wire:sort`

The old `wire:sortable` directive (which came from the third-party `@wotz/livewire-sortablejs` library) has been replaced with Livewire 4's native `wire:sort`. The external CDN script is no longer loaded.

If you have custom code using the old directive, migrate:

| Old (`@wotz/livewire-sortablejs`) | New (Livewire 4 native) |
| :-------------------------------- | :---------------------- |
| `wire:sortable="method"`          | `wire:sort="method"`    |
| `wire:sortable.item="id"`         | `wire:sort:item="id"`   |
| `wire:sortable.handle`            | `wire:sort:handle`      |
| `wire:sortable.options="{...}"`   | (removed — animations built in) |

Sort handler signatures change from `(array $items)` to `($id, $position)`. `$position` is **zero-based** and the method is called once per moved item.

### Quill Alpine component renamed

The package's Quill-based elements (`Text`, `HeadlineText`) now register an Alpine component named `pageComposerEditor` instead of `quillEditor`. This avoids collisions with host apps that already define their own `quillEditor` Alpine component.

**If you published element views** (`vendor:publish --tag=page-composer-elements`), update your published copies:

```blade
<!-- before -->
<div x-data="quillEditor({})">

<!-- after -->
<div x-data="pageComposerEditor({})">
```

### Minimum Laravel / Livewire / PHP versions

Support for Laravel 10–12, Livewire 3, and PHP 8.1–8.2 has been dropped. See the [Upgrading from 0.1.x to 1.x](README.md#upgrading-from-01x-to-1x) section of the README for the full migration guide.

## New Features

### Configurable Quill toolbar

Quill's toolbar is now configurable via the `quill_toolbar` config key. The default covers headings (Normal / H1–H3), inline formatting (bold/italic/underline), ordered + bullet lists, links, and a clear-formatting button:

```php
'quill_toolbar' => [
    [['header' => [false, 1, 2, 3]]],
    ['bold', 'italic', 'underline'],
    [['list' => 'ordered'], ['list' => 'bullet']],
    ['link'],
    ['clean'],
],
```

Override the array in your published config to add, remove, or rearrange groups — see [Quill's toolbar docs](https://quilljs.com/docs/modules/toolbar/) for the full syntax.

### Native Livewire 4 drag & drop

No external JS library is required anymore. The package ships with Livewire 4's built-in `wire:sort` for row and column reordering. Smooth animations are enabled by default.

## Bug Fixes

- **Cache hydration across framework upgrades**: Lookup tables (languages, elements, categories, tags) now cache plain arrays (`->toArray()`) and rehydrate fresh model instances on read. This prevents `__PHP_Incomplete_Class` errors on `Illuminate\Database\Eloquent\Collection` when cache entries span framework or cache-driver changes.
- **Blade parse error**: Fixed a `ParseError` in `page-composer.blade.php` caused by a nested inline array default inside `@json(config(...))`.
- **Packagist rejection**: Removed the hardcoded `version` field from `composer.json` so Packagist derives the version from git tags. This is what prevented 1.0.0 from landing on Packagist.
- **Livewire 4 serialization**: Eliminated stale serialization patterns introduced by Livewire 3 — `wire:model.defer` (47 instances), `$queryString` on `BugComponent`, and legacy `get*Property()` accessors.

### Quality-of-life fixes inherited from the 1.0.0 attempt

These landed in the 1.0.0 tag and are still present in 1.0.1:

- Replaced `uniqid()` with stable keys to stop Livewire components from re-mounting on every render (20+ call sites)
- Replaced `uniqid()` with `Str::ulid()` / `Str::random(8)` for filenames and element IDs (5 sites)
- Fixed 9 files with `namespace Foo\Bar;;` (double-semicolon syntax errors)
- Fixed `setInterval` memory leak in flash message auto-hide (now `setTimeout`)
- Fixed unreachable `return false` in `ImageUploadComponent::imageExists()`
- Fixed `$comlumn_key` typo in `ElementList`
- Removed commented-out debug code, dead code blocks, and stale service provider entries

## Upgrade Guide

1. Bump your app to PHP 8.3+, Laravel 13, and Livewire 4.
2. Run `composer update flobbos/page-composer`.
3. Clear everything:
   ```bash
   php artisan cache:clear
   php artisan view:clear
   php artisan config:clear
   php artisan optimize:clear
   ```
4. If you published element views, rename `quillEditor` → `pageComposerEditor` in the published copies.
5. If you built custom `wire:sortable` code, migrate to `wire:sort` (see the table above).

See the full migration guide in the [README](README.md#upgrading-from-01x-to-1x).
