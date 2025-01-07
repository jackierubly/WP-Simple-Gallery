<?php

/**
 * Plugin Name: Basic Simple Gallery 
 * Description: A simple gallery plugin with Title, Subtitle, and Image. 
 * Version: 1.2
 * Author: Jackie Rubly
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define plugin constants
define('SIMPLE_GALLERY_PATH', plugin_dir_path(__FILE__));
define('SIMPLE_GALLERY_URL', plugin_dir_url(__FILE__));

// Include necessary files
include_once SIMPLE_GALLERY_PATH . 'includes/admin.php';
include_once SIMPLE_GALLERY_PATH . 'includes/shortcode.php';

add_action('wp_enqueue_scripts', 'simple_gallery_enqueue_styles');
function simple_gallery_enqueue_styles()
{
    wp_enqueue_style('simple-gallery-style', SIMPLE_GALLERY_URL . 'assets/style.css');
}

add_action('admin_menu', 'simple_gallery_menu');

function enqueue_bootstrap_assets()
{
    // Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    // Bootstrap JavaScript (Optional for advanced interactivity)
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', [], null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_bootstrap_assets');

function enqueue_media_uploader_scripts()
{
    wp_enqueue_media(); // Ensure the WordPress Media Uploader is available
    wp_enqueue_script(
        'media-uploader',
        plugin_dir_url(__FILE__) . 'includes/media-uploader.js', // Path to your JavaScript file
        ['jquery'], // Dependencies
        null,
        true // Load in the footer
    );
}
add_action('admin_enqueue_scripts', 'enqueue_media_uploader_scripts');

function enqueue_lightbox_assets()
{
    // Lightbox2 CSS
    wp_enqueue_style('lightbox-css', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css');
    // Lightbox2 JS
    wp_enqueue_script('lightbox-js', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js', [], null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_lightbox_assets');

function enqueue_copy_button_script()
{
    wp_enqueue_script('clipboard-js', 'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js', [], null, true);
    wp_add_inline_script('clipboard-js', '
        document.addEventListener("DOMContentLoaded", function () {
            new ClipboardJS(".copy-shortcode");
        });
    ');
}
add_action('admin_enqueue_scripts', 'enqueue_copy_button_script');
