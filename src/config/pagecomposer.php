<?php

/**
 * Main Page Composer config file
 *
 * All relevant settings are changed and updated here.
 *
 */

return [
    /**
     * Define the minimum required information
     */
    'rules' => [
        'page.name' => 'required', //mandatory
        'page.photo' => 'required',
        'page.slider_image' => 'sometimes:image',
        'page.newsletter_image' => 'sometimes:image',
        'pageTranslations.*.content.title' => 'required', //mandatory
        'page.category_id' => 'required', //remove if not using categories
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
     * Run the selected middleware
     */
    'middleware' => 'auth:sanctum',

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
];
