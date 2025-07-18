<?php

return [
    /**
     * This prefix will be applied when setting and getting all cookies.
     * If not set, the cookies will not be prefixed.
     * If set, a good strategy is to also add a trailing underscore "_", that will be added between the field value, and each cookie.
     * For example, if `cookie_prefix` is set to `my_app_`, then the cookies will be stored in a JSON object with the key `my_app_cookies_consent_selection`.
     * Example:
     *
     * {
     *    "my_app_cookies_consent_selection": {
     *       "strictly_necessary": true,
     *      "performance": false,
     *     "targeting": false
     *   }
     * }
     */
    'cookie_prefix' => 'deployai_bn_',
    'display_floating_button' => false, // Set to false to display the footer link instead
    'hide_floating_button_on_mobile' => false, // Set to true to hide the floating button on mobile
    'use_separate_page' => false, // Set to true to use a separate page for cookies explanation
    'categories_collapsed_by_default' => true, // Set to false to initially collapse the optional categories
    'cookie_policy_page_custom_url' => null, // Set the custom page URL if use_separate_page is set to true and you want to use a custom URL or Laravel route
    /*
    |--------------------------------------------------------------------------
    | Editor
    |--------------------------------------------------------------------------
    |
    | Choose your preferred cookies to be shown. You can add more cookies as desired.
    | If, for example you add another cookie with the name "marketing", then you should also
    | publish the translation files and add a "cookie_marketing" key in the translation file,
    | since the plugin will try to display the cookie name by this convention.
    |
    | Built-in: "strictly_necessary"
    |
    */
    'cookies' => [
        'strictly_necessary' => [
            [
                'name' => 'deployai_itl_cookies_consent',
                'description' => 'cookies_consent::messages.cookie_cookies_consent_description',
                'duration' => 'cookies_consent::messages.years',
                'duration_count' => 1,
                'policy_external_link' => 'https://www.deployaiproject.eu/privacy-policy/',
            ],
            [
                'name' => 'XSRF-TOKEN',
                'description' => 'cookies_consent::messages.cookie_xsrf_token_description',
                'duration' => 'cookies_consent::messages.hours',
                'duration_count' => 2,
                'policy_external_link' => 'https://www.deployaiproject.eu/privacy-policy/',
            ],
            [
                'name' => 'laravel_session',
                'description' => 'cookies_consent::messages.cookie_laravel_session_description',
                'duration' => 'cookies_consent::messages.hours',
                'duration_count' => 2,
                'policy_external_link' => 'https://www.deployaiproject.eu/privacy-policy/',
            ],
        ],
    ],
    'enabled' => [
        'strictly_necessary',
    ],
    'required' => ['strictly_necessary'],
    /*
     * Set the cookie duration in days.  Default is 365 days.
     */
    'cookie_lifetime' => 365,
];
