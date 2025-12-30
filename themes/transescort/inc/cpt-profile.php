<?php
defined('ABSPATH') || exit;
/**
 * TE FILE: inc/cpt-profile.php
 * CPT registration for Profile.
 * Loaded via functions.php
 */


add_action('init', function () {
    register_post_type('profile', [
        'labels' => [
            'name'          => 'Profiles',
            'singular_name' => 'Profile',

        ],
        'public'             => true,
        'publicly_queryable' => true,
        'exclude_from_search'=> false,
        'query_var'          => true,

        'has_archive'   => true,
        'rewrite'       => ['slug' => 'profiles'],
        'menu_icon'     => 'dashicons-id',
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
        'show_in_rest'  => true,
    ]);
});
