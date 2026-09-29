# PageComposer

![Page Composer](img/page-composer.png)

**Handle your content a little differently**

This package aims to create a flexible CMS experience for the user as well as the developer. Content is divided into rows and columns which contain elements of your choosing, text, photo, video and elements you can create based on your needs. This is a different approach at handling website content. I hope you like it.

### Docs

- [Installation](#installation)
- [Dependency configuration](#dependency-configuration)
- [Laravel layout](#laravel-layout)
- [Livewire](#livewire)
- [Configuration](#configuration)
- [Laravel compatibility](#laravel-compatibility)
- [Upgrading to 3.0](#upgrading-to-30)
- [Upgrading to 2.1](#upgrading-to-21)
- [Upgrading to 2.0.2](#upgrading-to-202)
- [Upgrading from 1.x to 2.x](#upgrading-from-1x-to-2x)
- [Upgrading from 0.1.x to 1.x](#upgrading-from-01x-to-1x)

## Installation

### Install package

Add the package in your composer.json by executing the command.

```bash
composer require flobbos/page-composer
```

PageComposer features auto discover for Laravel. In case this fails, just add the
Service Provider to the app.php file.

```
Flobbos\PageComposer\PageComposerServiceProvider::class,
```

### Running the installation routine

Run the following install command.

```bash
php artisan page-composer:install
```

If you're asked for a name of the installation just make something up. No further steps are required
everything's automated.

### Recalculate row available space

If you changed column layouts or suspect stale `available_space` values in existing content,
run:

```bash
php artisan page-composer:sync-row-space
```

Use dry-run mode to preview updates without writing to the database:

```bash
php artisan page-composer:sync-row-space --dry-run
```

### Publish configuration file

This will publish all necessary files and assets needed for getting up and running. Just select the PageComposerServiceProvider
and you should be good to go.

```bash
php artisan vendor:publish
```

## Dependency configuration

### TranslatableDB

The package relies on flobbos/translatable-db to handle translations. It's important
to configure this package as well. For this you need to run:

```bash
php artisan vendor:publish
```

Select the Flobbos\TranslatableDb package. It will publish a configuration file to
which you need to add the following path:

```php
'language_model' => 'Flobbos\PageComposer\Models\Language',
```

This way the language model will be detected correctly and translations can be loaded.

### Livewire

Please also check the [Livewire](#livewire) section for two very important config settings
to make things work correctly.

### Tailwind

Additionally you will need to add the package views to your TailwindCSS configuration
so everything is compiled correctly. In the contents section of the config file please
add the following line:

```php
"./vendor/flobbos/page-composer/src/resources/views/**/*.blade.php",
```

This will let Tailwind know where to look for files to check for classnames and such.

### Laravel layout

PageComposer injects a few snippets onto the scripts stack in order to make the default components work like the editor for example. For this to work correctly you need to add the following to your default layout:

```php
@stack('scripts')
```

Either at the top or bottom of your layout file.

We also need to inject a few styles to make the editor work so please add the following to the top of your layout
after your regular styles.

```php
@stack('styles')
```

### Migrations

The package loads its migrations directly; there's nothing to publish. All tables are
prefixed with `pagecomposer.table_prefix` (default `pc_`), so set that before you migrate
if you want a different prefix.

```bash
php artisan migrate
```

### Adding the package

### Routes

The routes will be automatically loaded from the package folder. The middleware on these
routes comes from the config and defaults to `['web', 'auth']`:

```php
'middleware' => ['web', 'auth'],
```

> ⚠️ **Authorization is up to your app.** With `auth` alone, every logged-in user can
> create, publish and delete pages. Restrict access with an ability your app defines:
>
> ```php
> // config/pagecomposer.php
> 'middleware' => ['web', 'auth', 'can:manage-pages'],
>
> // AppServiceProvider::boot()
> Gate::define('manage-pages', fn (User $user) => $user->is_admin);
> ```
>
> This also covers every Livewire action on those pages: Livewire re-runs `auth` and
> `can:` middleware on its update requests.

With Laravel Jetstream installed with the default configuration, that might look like:

```php
'middleware' => [
        'web',
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
        'can:manage-pages',
    ]
```

### Menu entries

There's no default menu provided with the package. You need to add these entries yourself.
The following routes must be added to access the PageComposer:

```php
route('page-composer::pages.index');
route('page-composer::pages.create');
route('page-composer::pages.edit',$page_id);
```

If you want to use the default preview route, you need to add the following route:

```php
route('page-composer::pages.detail', $slug);
```

The preview route renders inside the layout set in `pagecomposer.frontend_layout`
(default `layouts.frontend`). If your app has its own
`resources/views/livewire/frontend/page-display.blade.php`, that view is used instead of
the package's.

`route('page-composer::dashboard')` points at the page list.

There's also a built-in micro bug tracker where users can report bugs or ask for new
elements. It's off by default; turn it on with `'bug_tracker' => true` and link to:

```php
route('page-composer::bugs');
```

## Configuration

The configuration options have been kept fairly simple at the moment. The following
options are available:

### Validation rules

Here you can set some basic validation options that will be used for saving a page.

```php
'rules' => [
        'pageData.name' => 'required', //mandatory
        'pageData.photo' => 'required',
        'pageData.slider_image' => 'nullable|string',
        'pageData.newsletter_image' => 'nullable|string',
        'pageTranslations.*.content.title' => 'required', //mandatory
        'pageData.category_id' => 'required', //remove if not using categories
    ],
```

> The rule keys reference the public `$pageData` array on the PageComposer
> Livewire component. The `pageData.*` prefix is mandatory — bare `name`,
> `photo`, etc. won't match. (See the 1.x → 2.x upgrade notes if you're
> coming from 1.0.x where these were keyed under `page.*`.)

### FAQ

There's a small FAQ to help people get started. If you want to show this:

```php
   'showFaq' => true,
```

### Tags

If you want to use the tags provided by the package for the pages created:

```php
    'useTags' => true,
```

### Categories

PageComposer comes with a default categorisation option. If you want to use it:

```php
    'useCategories' => true,
```

### Element Creator

PageComposer provides stubs for creating new content elements. These will of course
just create a blank element template which you need to update. This option might be a
bit counter intuitive for the regular users if made available during production.

```php
    'showElementCreator' => true,
```

Element names may only contain letters, numbers, spaces, hyphens and underscores, and must
start with a letter, since they become class and file names. The same applies to
`php artisan page-composer:element`.

By default the element creator only registers components you've already built (with
`php artisan page-composer:element`, then filled in). To let it generate the class and view
files itself, which you probably only want in local development:

```php
    'allow_web_scaffolding' => true,
```

Element icons are raw SVG. Anything that isn't plain presentational SVG (scripts, event
handlers, `foreignObject`) is stripped when the icon is saved or displayed.

### Content sanitizing

Element content comes from the browser, and some element views render it as HTML, so it's
cleaned before it's saved:

- Keys listed in `sanitize.html_keys` go through an HTML allowlist covering what the Quill
  toolbar produces. If your own elements render other keys with `{!! !!}`, add them here.
- Any key ending in `url` (`ctaUrl`, `videoUrl`, `imageUrl`...) only keeps `http`, `https`,
  `mailto`, `tel` or relative URLs, because Blade escaping doesn't stop `javascript:` links.
- Everything else is stored as is and escaped by Blade on output.

```php
'sanitize' => [
    'enabled' => true,
    'html_keys' => ['text', 'videoCaption'],
    'url_schemes' => ['http', 'https', 'mailto', 'tel'],
    'allowed_elements' => [
        'p' => ['class'],
        'a' => ['href', 'target', 'rel'],
        // ...
    ],
],
```

### Column Presets

The row editor column buttons are configurable. A default set is included, and you can add or override presets in `config/pagecomposer.php`:

```php
'column_presets' => [
    [
        'size' => 12,
        'label' => 'Full',
        'preview_segments' => 1,
        'group' => 'full',
        'requires_empty' => true,
    ],
    [
        'size' => 6,
        'label' => 'Half',
        'preview_segments' => 2,
        'group' => 'halves_quarters',
    ],
    [
        'size' => 5,
        'label' => '5/12',
        'preview_segments' => 5,
    ],
],
```

Notes:

- `size` is the column width in twelfths (`1` to `12`).
- Presets with the same `size` override the default for that size.
- `group` lets you keep row layouts compatible by only mixing presets from one group.
- `requires_empty` shows that preset only when the row has no columns yet.

### Column Width Classes

You can also override how each column size maps to Tailwind width classes:

```php
'column_widths' => [
    12 => 'w-full',
    9 => 'w-3/4',
    8 => 'w-2/3',
    6 => 'w-1/2',
    4 => 'w-1/3',
    3 => 'w-1/4',
],
```

Any missing size falls back to `w-full`.

### Quill Editor Toolbar

The Text and HeadlineText elements use [Quill](https://quilljs.com/) for rich text editing. The toolbar is configurable via the `quill_toolbar` key, which is passed directly to Quill's `modules.toolbar` option. The default covers common formatting needs:

```php
'quill_toolbar' => [
    [['header' => [false, 1, 2, 3]]],
    ['bold', 'italic', 'underline'],
    [['list' => 'ordered'], ['list' => 'bullet']],
    ['link'],
    ['clean'],
],
```

That gives you a Normal / H1–H3 dropdown, inline formatting (bold/italic/underline), ordered and bullet lists, links, and a clear-formatting button. Override the array in your published config to add, remove, or rearrange groups — see [Quill's toolbar docs](https://quilljs.com/docs/modules/toolbar/) for the full syntax.

#### Alpine component name

The package registers a namespaced Alpine component called **`pageComposerEditor`** for its Quill-based elements. This avoids collisions with host apps that already register their own `quillEditor` (or similarly-named) Alpine component — your existing editors keep working untouched.

If you publish a copy of the Text or HeadlineText elements and want them to pick up future package changes, make sure your published copy still uses `x-data="pageComposerEditor({})"`.

## Livewire

The package relies on Livewire 4 and Alpine 3.

### Layout

All full page components use the classic layout path which differs from the default
layout path suggested by Livewire 3. Set the following option for the correct layout path:

```php
'layout' => 'layouts.app',
```

## Laravel compatibility

| Laravel | PageComposer |
| :------ | :----------- |
| 13.x    | 3.x, 2.x, 1.x |
| 10-12.x | 0.1.x        |

PageComposer 2.x and 1.x both require Laravel 13, Livewire 4, and PHP 8.3+. 2.x is a structural rewrite of the editor component (now broken into traits + services with a typed property surface) and adds a Pest 4 test suite. See the upgrade notes below for the breaking changes.

## Upgrading to 3.0

3.0 keeps the Laravel 13 / Livewire 4 / PHP 8.3 baseline. The breaking changes are about
living alongside your app's own tables and components.

### 1. Tables are prefixed

Package tables move under `pagecomposer.table_prefix`, default `pc_` (`pc_pages`,
`pc_rows`, `pc_tags`...). A migration renames the existing tables; foreign keys follow the
rename. **Decide on the prefix before you run `php artisan migrate`.** To keep the old
table names, publish the config and set:

```php
'table_prefix' => '',
```

If your own code queries the package tables by name (raw queries, `exists:` rules, joins),
use the models' `getTable()` or the new names.

### 2. Livewire components are namespaced

Package components are registered as `page-composer::name` instead of bare global names
like `date-picker`, which could collide with your app's components. If your own views
embed package components, update the tags:

```diff
- <livewire:image-upload-component ... />
+ <livewire:page-composer::image-upload-component ... />
```

Your published elements (`page-composer-elements.*`) aren't affected.

### 3. Slugs are unique per language

A unique index now covers `(language_id, slug)`. The migration resolves existing
duplicates first: the oldest translation keeps the slug and later ones get `-2`, `-3` and
so on, which **changes the URL of those later pages**. Empty slugs become `NULL`.

### 4. The bug tracker is opt-in

The bug tracker is off by default and `page-composer::dashboard` now points at the page
list. To keep using it, set `'bug_tracker' => true`; it lives at `page-composer::bugs`
(`/page-composer/bugs`). Bug notification links point there too.

### 5. Smaller changes

- Migrations are no longer publishable. If you published them before, the copies are
  harmless (they share filenames with the package's), but they're no longer needed.
- The element stubs moved to `src/resources/stubs/elements`. The `page-composer-elements`
  publish tag still works the same way.
- `changelog.md` is now `CHANGELOG.md`, and the old `RELEASE-*.md` files are gone.

## Upgrading to 2.1

2.1 changes behaviour in a few places but needs no code changes in your app. Run the
migration and read through the list:

```bash
php artisan migrate
```

- **Element content is sanitized on save.** Rich-text keys lose anything outside the
  allowlist, and `*Url` keys lose unsafe schemes. Content that's already stored is cleaned
  the next time its page is saved. If your own elements render other keys as HTML, add them
  to `sanitize.html_keys`. See [Content sanitizing](#content-sanitizing).
- **The element creator no longer writes files by default.** Set `allow_web_scaffolding` to
  `true` to get the old behaviour back. With it off, renaming an element keeps its
  component name, so it keeps pointing at your files.
- **Slugs are unique per language.** Saving a page whose title another page already uses
  in the same language gives it `title-2`, `title-3` and so on. Existing duplicates stay
  until those pages are saved again. A new index speeds up the preview route's slug lookup.
- **Bug tracker users come from `auth.providers.users.model`**, not a hardcoded
  `App\Models\User`, and notifications to a user that doesn't exist are skipped instead of
  throwing.
- **New dependency:** `symfony/html-sanitizer`.

## Upgrading to 2.0.2

2.0.2 is a security release. It changes no public API, but a few things live in files
that were published into your app, and `composer update` doesn't touch those. Run this
after updating to see what still needs attention:

```bash
php artisan page-composer:doctor
```

### 1. Patch your published Photo element

The Photo element saved any uploaded file, including `.php` files, to the public disk
under the client-supplied extension. If you published the elements, update
`app/Livewire/PageComposerElements/Photo.php` (or copy the package's
`src/PageComposer/Livewire/Elements/Photo.php`, or `src/resources/stubs/elements/Photo.php` from 3.0 on, over it if you never changed it):

```php
use Illuminate\Validation\ValidationException;

protected function photoRules(): array
{
    return [
        'photo' => 'required|image|max:2048',
    ];
}

// Drops a bad file as soon as it's picked, before the preview tries to render it
public function updatedPhoto()
{
    try {
        $this->validate($this->photoRules());
    } catch (ValidationException $e) {
        $this->reset('photo');

        throw $e;
    }
}

public function savePhoto()
{
    $this->validate($this->photoRules());

    //Delete existing photo if replaced
    if (!empty($this->data['content']['photo'])) {
        $this->deleteExistingPhoto();
    }

    //Randomize filename, taking the extension from the file's contents
    //rather than the client-supplied name
    $filename = Str::slug(pathinfo($this->photo->getClientOriginalName(), PATHINFO_FILENAME))
        . '_' . Str::ulid() . '.' . $this->photo->extension();

    //Save photo
    $this->photo->storeAs('photos', $filename, 'public');
    $this->data['content']['photo'] = $filename;

    $this->reset('photo');
}

public function deleteExistingPhoto()
{
    Storage::disk('public')->delete('photos/' . basename((string) $this->data['content']['photo']));
    Arr::set($this->data, 'content.photo', null);
}
```

### 2. Check your published config

- **`middleware`**: the default is now `['web', 'auth']`. If your published config still
  says `'auth:sanctum'` without `'web'`, sessions and CSRF protection don't run on the
  Page Composer routes. Add `'web'`, and see [Routes](#routes) for restricting access.
- **`rules`**: `'sometimes:image'` validates nothing. Those fields hold a stored path, so
  use `'nullable|string'`.

### 3. Run the new migration

```bash
php artisan migrate
```

`pages.category_id` used to cascade on delete, so deleting a category hard-deleted
every page in it. Pages now keep existing with no category, and the editor asks for a
new one on the next save.

### 4. If you published the migrations

The package migrations are now anonymous classes, which stops them colliding with
your own `CreateTagsTable`, `CreateCommentsTable` and so on. Migrations that already
ran are tracked by filename, so nothing re-runs. Published copies keep their old
class names; re-publish them or convert them yourself if you hit a
"Cannot declare class" error.

## Upgrading from 1.x to 2.x

2.x keeps the same Laravel / Livewire / PHP minimums as 1.x, but ships several breaking changes from a structural rewrite of the `PageComposer` Livewire component. None of them touch the database; the migrations are unchanged.

> ⚠️ **Required before deploying — raise your Livewire payload limits.** 2.x mounts a nested Livewire component for every row, column, and element, so a single non-trivial page far exceeds Livewire's default `payload` caps. Without this, saving or updating any page with more than ~20 components throws `Livewire\Exceptions\TooManyComponentsException` (a hard 500) — e.g. a 34-row page is ~75 components against a default cap of 20. The deep property paths (`rows.{locale}.rows.0.columns.0.column_items.0.content.*`) also sit right at the default nesting-depth limit. In your published `config/livewire.php` (run `php artisan livewire:publish --config` first if needed):
>
> ```php
> 'payload' => [
>     'max_size' => 5 * 1024 * 1024, // was 1MB
>     'max_nesting_depth' => 20,     // was 10 — composer paths are ~10 deep
>     'max_calls' => 200,            // was 50
>     'max_components' => 1000,      // was 20 — set comfortably above your largest page's row+column+element count
> ],
> ```

### 1. Validation rule keys: `page.*` → `pageData.*`

The public property holding the form state was renamed from `$page` to a typed `?array $pageData = null`, distinct from `mount`'s `$page` route-binding parameter. **If you published the config**, update the keys in your `config/pagecomposer.php`:

```diff
 'rules' => [
-    'page.name' => 'required',
-    'page.photo' => 'required',
-    'page.slider_image' => 'sometimes:image',
-    'page.newsletter_image' => 'sometimes:image',
+    'pageData.name' => 'required',
+    'pageData.photo' => 'required',
+    'pageData.slider_image' => 'sometimes:image',
+    'pageData.newsletter_image' => 'sometimes:image',
     'pageTranslations.*.content.title' => 'required',
-    'page.category_id' => 'required',
+    'pageData.category_id' => 'required',
 ],
```

If you have custom validation messages or translation keys referencing `page.*`, rename those too.

### 2. Row / column children now bind via `wire:model`, not `:row=` / `:column=`

`RowComponent::$row` and `ColumnComponent::$column` are now `#[Modelable]`. The whole dispatch-back-up chain (`itemsUpdated.{target}` → `columnUpdated` → `rowUpdated`) has been removed.

If you customized either view, update the child tags:

```diff
- <livewire:row-component :row="$row" :rowKey="$rowKey" :previewMode="$previewMode" />
+ <livewire:row-component wire:model="rows.{{ $currentLanguage->locale }}.rows.{{ $rowKey }}" :rowKey="$rowKey" :previewMode="$previewMode" />

- <livewire:column-component :column="$column" :columnKey="$columnKey" target="{{ $source }}" />
+ <livewire:column-component wire:model="row.columns.{{ $columnKey }}" :columnKey="$columnKey" />
```

The `target` attribute on `<livewire:column-component>` is gone, and `RowComponent::$source` no longer exists.

If your code dispatched or listened for `rowUpdated`, `columnUpdated`, or `itemsUpdated.*`, those events are no longer in the parent/child sync path. Use Modelable on your own components or fall back to direct property writes through the bound state.

### 3. Image upload now needs `fieldName`

`ImageUploadComponent` includes `fieldName` in its dispatched payload, and `PageComposer`'s listeners use it to route the value to the right photo field. Update any custom view that mounts an upload component for the page composer:

```diff
 <livewire:image-upload-component
     existingImage="{{ $photo }}"
     eventTarget="pageComposer.mainPhoto"
+    fieldName="photo"
     imagePath="photos/" />
```

Allowed `fieldName` values for the bundled `PageComposer` listener: `photo`, `newsletter_image`, `slider_image`. Any other value is silently ignored.

### 4. Several public properties are now `#[Locked]`

These are server-controlled and reject frontend writes via Livewire payloads: `$elements`, `$pageId`, `$exceptionMessage`, `$showErrorMessage`, `$categories`, `$tags`, `$photo`, `$newsletter_image`, `$slider_image`, `$languages`, `$currentLanguage`, `$availableLanguages`, `$selectableLanguages`. If you had a `wire:model` pointed at any of these, it will throw `CannotUpdateLockedPropertyException`. Use the appropriate event (`addLanguage`, `setLanguage`, the `eventImageUploadComponent*` events, etc.) instead.

### 5. Save / update errors are now sanitized

Previously the user saw `$ex->getMessage() . ' ' . $ex->getLine() . ' ' . $ex->getFile()` in a flash error and on-page banner, which leaked filesystem paths. 2.x reports the exception via `report($ex)` and shows a generic message ("We could not save this page. Please try again."). Configure your error reporter (Sentry, Bugsnag, etc.) if you want the detail elsewhere.

### 6. New service classes

Persistence and caching moved out of the Livewire component:

- `Flobbos\PageComposer\Services\PageBuilder` — transactional save/update of Page + translations + rows + columns + items
- `Flobbos\PageComposer\Services\PageBuilderResult` — readonly DTO returned by `PageBuilder::persist`
- `Flobbos\PageComposer\Services\PageComposerCache` — single entry point for `elements()`, `languages()`, `categories()`, `tags()`, plus `forgetAll()`
- `Flobbos\PageComposer\Services\SortService` — the row/column reorder algorithm

If you extended the editor component, you can resolve these via the container.

### 7. Component split into concern traits

`PageComposer` is now ~330 lines of orchestration plus four traits under `Flobbos\PageComposer\Livewire\Concerns`:

- `HandlesImageUploads` — `$photo`, `$newsletter_image`, `$slider_image` + the two listener pair + `setPhotoField()`
- `HandlesTemplates` — `saveTemplate`, `loadTemplate`, `selectTemplate`, `$templateName`, `$selectedTemplate`
- `InteractsWithLanguages` — language props, `addLanguage`, `setLanguage`, `setRowsLanguage`, `hydrateLanguages`, `copyContent`, `languageAdded` listener
- `ManagesRows` — `$rows`, `$showMiniMap`, `addRow`, `updateRowSorting`, sort helpers, `deleteRow` listener

If you extended `PageComposer` to override one of those methods, the override may now need to be on the trait or via a host-app trait that re-uses our trait.

### 8. Configurable date format

`DatePicker` and `PageComposer::dateSelected` no longer hard-code `m-d-Y`. They read `pagecomposer.date_format`, default `'m-d-Y'` for backward compatibility. Recommended for new installs:

```php
'date_format' => 'Y-m-d', // ISO 8601, locale-neutral
```

### 9. Pest plugins on the v4 line

The package's dev dependencies on `pestphp/pest-plugin-laravel` and `pestphp/pest-plugin-livewire` are now `^4.0`, requiring Pest 4. If you run `vendor/bin/pest` against the package, run `composer update` first.

## Upgrading from 0.1.x to 1.x

The 1.x line targets Laravel 13 and Livewire 4, which forced several breaking changes. The package itself handles the framework-level migrations, but a few things will need attention in your host app if you customized or published parts of the package.

### 1. Bump your platform

Make sure your app is on PHP 8.3+, Laravel 13, and Livewire 4 before upgrading. These are hard minimums.

### 2. Clear caches aggressively during the upgrade

Livewire 4 and Laravel 13 produce different serialized formats than their predecessors. After running `composer update`:

```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan optimize:clear
```

If you see a `__PHP_Incomplete_Class` error on `Illuminate\Database\Eloquent\Collection`, it's a stale cache entry from the old version — clear the cache driver and flush sessions.

### 3. Drag & drop migrated to `wire:sort`

1.0.0 temporarily relied on the old `wire:sortable` directive from `@wotz/livewire-sortablejs`; 1.0.1 replaces it with Livewire 4's native `wire:sort`. If you built custom pieces that mimicked the old directive or piggy-backed on the old JS library, you will need to migrate:

- `wire:sortable="method"` → `wire:sort="method"`
- `wire:sortable.item="id"` → `wire:sort:item="id"` (dot → colon)
- `wire:sortable.handle` → `wire:sort:handle`
- Sort handler signatures change from `(array $items)` to `($id, $position)`, where `$position` is zero-based and the method is called once per moved item

Remove any `@wotz/livewire-sortablejs` CDN script tags — they are no longer needed.

### 4. Quill Alpine component renamed

The package's built-in Quill-based elements (`Text`, `HeadlineText`) previously used an Alpine component named `quillEditor`. That name is common in host apps, so it has been renamed to `pageComposerEditor` in 1.0.1.

If you published those element views (via `vendor:publish --tag=page-composer-elements`) into your app, update the copies:

```blade
<div x-data="pageComposerEditor({})">
```

Custom elements you created under `app/Livewire/PageComposerElements/` are not affected unless they explicitly reference the old name.

### 5. Quill toolbar is now configurable

By default, the toolbar is restricted to a Normal / H1–H3 dropdown. If you need the old unrestricted toolbar back, set `quill_toolbar` in `config/pagecomposer.php` — see the [Quill Editor Toolbar](#quill-editor-toolbar) section above for examples.

### 6. Livewire deprecations removed

The package no longer uses `wire:model.defer`, `$queryString`, or the legacy `get*Property()` accessor pattern. These continued to work in Livewire 3 but are gone in Livewire 4. If you extended internal components, mirror the same patterns (`#[Url]`, `#[Computed]`, plain `wire:model`).
