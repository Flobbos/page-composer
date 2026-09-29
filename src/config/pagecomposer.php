<?php

/**
 * Main Page Composer config file
 *
 * All relevant settings are changed and updated here.
 *
 */

return [
    /**
     * Prefix for the package's database tables. Set it before running the
     * migrations; changing it afterwards needs a migration of your own.
     */
    'table_prefix' => 'pc_',

    /**
     * Define the minimum required information
     */
    'rules' => [
        'pageData.name' => 'required', //mandatory
        'pageData.photo' => 'required',
        // These hold the stored path string by the time the page is
        // validated, not an upload, so validate them as strings.
        'pageData.slider_image' => 'nullable|string',
        'pageData.newsletter_image' => 'nullable|string',
        'pageTranslations.*.content.title' => 'required', //mandatory
        'pageData.category_id' => 'required', //remove if not using categories
    ],

    /**
     * Show the page composer faq in the create/edit screen
     */
    'showFaq' => true,

    /**
     * Use tags in your pages
     */
    'useTags' => true,

    /**
     * Use categories for your pages
     */
    'useCategories' => true,

    /**
     * Show element creator. Remove if you don't want
     * to have all users add new elements to the system.
     */
    'showElementCreator' => true,

    /**
     * Let the element creator generate class and view files in your app
     * (the same files `php artisan page-composer:element` writes). Off by
     * default: with it off, the creator only registers components you have
     * already built, and editing an element never renames files. Turn it on
     * in local development if you want the old behaviour.
     */
    'allow_web_scaffolding' => false,

    /**
     * Middleware for the Page Composer routes. Also applied to every
     * Livewire action on those pages, since Livewire re-runs `auth` and
     * `can:` middleware on its update requests.
     *
     * Authorization is up to your app. With `auth` alone, EVERY logged-in
     * user gets full access (create, publish, delete). Add an ability that
     * your app defines, for example:
     *
     *     'middleware' => ['web', 'auth', 'can:manage-pages'],
     */
    'middleware' => ['web', 'auth'],

    /**
     * Element content is cleaned before it is saved, because it comes from
     * the browser and several element views render it unescaped.
     *
     * - html_keys: content keys rendered as HTML ({!! !!}); run through the
     *   allowlist below. Add your own elements' rich-text keys here.
     * - Keys ending in "url" only keep http(s), mailto, tel or relative URLs.
     * - allowed_elements: element => allowed attributes. The default covers
     *   what the Quill toolbar can produce.
     */
    'sanitize' => [
        'enabled' => true,
        'html_keys' => ['text', 'videoCaption'],
        'url_schemes' => ['http', 'https', 'mailto', 'tel'],
        'allowed_elements' => [
            'p' => ['class'],
            'br' => [],
            'h1' => ['class'],
            'h2' => ['class'],
            'h3' => ['class'],
            'strong' => [],
            'em' => [],
            'u' => [],
            's' => [],
            'ol' => ['class'],
            'ul' => ['class'],
            'li' => ['class', 'data-list'],
            'span' => ['class'],
            'a' => ['href', 'target', 'rel'],
        ],
    ],

    /**
     * Layout used by the built-in public preview route
     * (page-composer::pages.detail).
     */
    'frontend_layout' => 'layouts.frontend',

    /**
     * Date format used by the built-in date picker, both for the value
     * shown to the user and for the value dispatched/parsed when a date
     * is selected.
     *
     * Default keeps backwards compatibility with previous releases.
     * Recommended for new installs: 'Y-m-d' (ISO 8601, locale-neutral).
     */
    'date_format' => 'm-d-Y',

    /**
     * The built-in bug tracker (page-composer::bugs) where users can report
     * problems or ask for new elements. Off by default.
     */
    'bug_tracker' => false,

    /**
     * Person responsible for the bug component
     * Provide the user id
     */

    'bug_user' => 1,

    /**
     * Activate or deactivate notifications from the bug component
     */

    'bug_notifications' => true,

    /**
     * Tailwind class before sidebar is pinned to viewport top.
     * Example: top-16, top-20, top-24
     */
    'sidebar_top_offset_class' => 'top-24',

    /**
     * Tailwind class once sidebar is pinned.
     */
    'sidebar_top_pinned_class' => 'top-0',

    /**
     * Scroll threshold (in px) before switching from offset to pinned class.
     */
    'sidebar_top_sticky_threshold' => 24,

    /**
     * Tailwind width class map per 12-column grid size.
     * Used by the composer preview for rendering row and column widths.
     */
    'column_widths' => [
        12 => 'w-full',
        11 => 'w-11/12',
        10 => 'w-5/6',
        9 => 'w-3/4',
        8 => 'w-2/3',
        7 => 'w-7/12',
        6 => 'w-1/2',
        5 => 'w-5/12',
        4 => 'w-1/3',
        3 => 'w-1/4',
        2 => 'w-1/6',
        1 => 'w-1/12',
    ],

    /**
     * Column options shown in the row editor.
     *
     * Rules:
     * - size: width in twelfths (1-12)
     * - label: text shown in the picker
     * - preview_segments: number of visual blocks in picker preview
     * - group: optional compatibility group (only one group can be mixed in a row)
     * - requires_empty: if true, option only appears on an empty row
     */

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
            'size' => 4,
            'label' => '1/3',
            'preview_segments' => 3,
            'group' => 'thirds',
        ],
        [
            'size' => 3,
            'label' => '1/4',
            'preview_segments' => 4,
            'group' => 'halves_quarters',
        ],
    ],

    /**
     * Quill editor toolbar configuration.
     *
     * Passed directly to Quill's `modules.toolbar` option. Each top-level array
     * is a toolbar group. Use `false` inside a header dropdown to represent
     * the normal/paragraph option.
     *
     * Default: a single dropdown allowing Normal text + Heading 1-3.
     *
     * See https://quilljs.com/docs/modules/toolbar/ for the full syntax.
     */
    'quill_toolbar' => [
        [['header' => [false, 1, 2, 3]]],
        ['bold', 'italic', 'underline'],
        [['list' => 'ordered'], ['list' => 'bullet']],
        ['link'],
        ['clean'],
    ],
];
