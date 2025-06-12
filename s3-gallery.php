<?php
/**
 * Plugin Name: S3 Gallery
 * Description: Displays external image galleries from any S3-compatible storage
 * Version: 1.0.0
 * Author: RocketQuack
 * License: GPLv2 or later
 * Text Domain: s3-gallery
 */

 if (!defined('ABSPATH')) exit;

 // Lade Komponenten
 require_once plugin_dir_path(__FILE__) . 'includes/s3-client.php';
 require_once plugin_dir_path(__FILE__) . 'admin/settings-page.php';
 
 // Assets
 function s3_gallery_enqueue_assets() {
     wp_enqueue_script('s3-gallery-js', plugin_dir_url(__FILE__) . 'assets/frontend.js', [], null, true);
 }
 add_action('wp_enqueue_scripts', 's3_gallery_enqueue_assets');
 
 // Shortcode
 function s3_gallery_shortcode() {
     $folders = s3_list_folders(); // dynamisch aus Prefixes
     ob_start(); ?>
     <div class="s3-gallery-wrapper">
         <select id="s3-folder-select">
             <option value="">-- Ordner wählen --</option>
             <?php foreach ($folders as $f): ?>
                 <option value="<?= esc_attr($f) ?>"><?= esc_html($f) ?></option>
             <?php endforeach; ?>
         </select>
         <div id="s3-gallery"></div>
     </div>
     <?php return ob_get_clean();
 }
 add_shortcode('s3_gallery', 's3_gallery_shortcode');
