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
];
