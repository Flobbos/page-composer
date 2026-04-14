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

During the publishing process the migration for the newsletter_templates table
was also published. Add all fields you need and run the migration.

```bash
php artisan migrate
```

### Adding the package

### Routes

The routes will be automatically loaded from the package folder. However you may need to update
the middlewares being used on these routes. You can do this easily by editing the config like so:

```php
'middleware' => [
        'web',
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
    ]
```

In this example we have Laravel Jetstream installed with the default configuration.

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
route('page-composer::pages.detail',$page_id);
```

There's also a built in micro bug tracker for users of the package. There users can
report bugs or add wishes for new elements and such.

```php
route('page-composer::dashboard');
```

## Configuration

The configuration options have been kept fairly simple at the moment. The following
options are available:

### Validation rules

Here you can set some basic validation options that will be used for saving a page.

```php
'rules' => [
        'page.name' => 'required', //mandatory
        'page.photo' => 'required',
        'page.slider_image' => 'sometimes:image',
        'page.newsletter_image' => 'sometimes:image',
        'pageTranslations.*.content.title' => 'required', //mandatory
        'page.category_id' => 'required', //remove if not using categories
    ],
```

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

The Text and HeadlineText elements use [Quill](https://quilljs.com/) for rich text editing. The toolbar is configurable via the `quill_toolbar` key, which is passed directly to Quill's `modules.toolbar` option. The default exposes only a Normal / H1–H3 dropdown:

```php
'quill_toolbar' => [
    [['header' => [false, 1, 2, 3]]],
],
```

Add groups for more formatting options (see [Quill's toolbar docs](https://quilljs.com/docs/modules/toolbar/) for the full syntax):

```php
'quill_toolbar' => [
    [['header' => [false, 1, 2, 3]]],
    ['bold', 'italic', 'underline'],
    ['link'],
    [['list' => 'ordered'], ['list' => 'bullet']],
],
```

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
| 13.x    | 1.x          |
| 10-12.x | 0.1.x        |

PageComposer 1.x requires Laravel 13, Livewire 4, and PHP 8.3+. Use 1.0.1 or newer — 1.0.0 shipped broken and is superseded.

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
