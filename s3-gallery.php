<?php
/**
 * Plugin Name: S3 Gallery
 * Description: Display of a gallery of images from an S3-compatible storage
 * Version: 1.1.0
 * Author: RocketQuackIT
 * License: GPLv2 or later
 */

defined('ABSPATH') || exit;

// Plugin-Komponenten laden
require_once plugin_dir_path(__FILE__) . 'admin/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/s3-client.php';
require_once plugin_dir_path(__FILE__) . 'templates/gallery.php';

// Optional: frontend.js laden
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('s3-gallery-frontend', plugin_dir_url(__FILE__) . 'public/frontend.js', [], null, true);
});
