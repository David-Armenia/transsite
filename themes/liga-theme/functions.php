<?php

function liga_assets() {
    wp_enqueue_style(
        'liga-main',
        get_template_directory_uri() . '/assets/css/main.css',
        [],
        '1.0'
    );
}

add_action('wp_enqueue_scripts', 'liga_assets');

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'google-fonts',
        'https://fonts.googleapis.com/css2?family=Bagel+Fat+One&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap'
    );
});


add_action('customize_register', function ($wp_customize) {

    $wp_customize->add_section('liga_home', [
        'title' => 'Homepage Content',
        'priority' => 30,
    ]);

    $wp_customize->add_setting('hero_title', [
        'default' => 'Trans Escort',
    ]);

    $wp_customize->add_control('hero_title', [
        'label' => 'Hero Title',
        'section' => 'liga_home',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('hero_text', [
        'default' => 'Discover verified profiles, real photos and trusted recommendations.',
    ]);

    $wp_customize->add_control('hero_text', [
        'label' => 'Hero Text',
        'section' => 'liga_home',
        'type' => 'textarea',
    ]);
});
