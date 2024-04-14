<?php

function getViteDevServerAddress()
{
    if (str_contains($_SERVER['HTTP_HOST'], 'local')) {
        return 'http://localhost:3000';
    }
    return '';
}

function isViteHMRAvailable()
{
    return !empty(getViteDevServerAddress());
}

function loadJSScriptAsESModule($script_handle)
{
    add_filter(
        'script_loader_tag',
        function ($tag, $handle, $src) use ($script_handle) {
            if ($script_handle === $handle) {
                return sprintf(
                    '<script type="module" src="%s"></script>',
                    esc_url($src)
                );
            }
            return $tag;
        },
        10,
        3
    );
}

if (!isViteHMRAvailable()) {
    return;
}

add_filter('stylesheet_uri', function () {
    return getViteDevServerAddress() . '/styles/main.scss';
});

const VITE_HMR_CLIENT_HANDLE = 'vite-client';
function loadScript()
{
    wp_enqueue_script(VITE_HMR_CLIENT_HANDLE, getViteDevServerAddress() . '/@vite/client', array(), null);
    loadJSScriptAsESModule(VITE_HMR_CLIENT_HANDLE);
}
add_action('wp_enqueue_scripts', 'loadScript');

// $manifest = json_decode(file_get_contents('/dist/manifest.json'));

function enqueue_scripts_styles()
{
    wp_enqueue_style('theme-style', get_stylesheet_directory_uri() . '/style.css', array(), null);
    if (isViteHMRAvailable()) {
        $handle = 'index';
        loadJSScriptAsESModule($handle);
        wp_enqueue_script($handle, getViteDevServerAddress() . '/assets/scripts/main.js', array('jquery'), null);
        wp_enqueue_style('style', getViteDevServerAddress() . '/assets/styles/main.scss', null);
    } else {
        $indexJS = 'index';
        $indexCSS = 'style.css';
        wp_enqueue_script($indexJS, get_stylesheet_directory_uri() . $GLOBALS['manifest']->$indexJS, array('jquery'), null);
        wp_enqueue_style($indexCSS, get_stylesheet_directory_uri() . $GLOBALS['manifest']->$indexCSS, array(), null);
    }
}

add_action('wp_enqueue_scripts', 'enqueue_scripts_styles', 20);
