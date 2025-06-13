<?php
if (!defined('ABSPATH')) exit;

function s3_gallery_register_shortcode() {
    add_shortcode('s3_gallery', function($atts) {
        $atts = shortcode_atts([
            'folder' => '',
        ], $atts);

        $config = [
            'endpoint'   => get_option('s3_gallery_endpoint'),
            'region'     => get_option('s3_gallery_region'),
            'bucket'     => get_option('s3_gallery_bucket'),
            'access_key' => get_option('s3_gallery_key'),
            'secret_key' => get_option('s3_gallery_secret'),
        ];

        if (in_array('', $config, true)) {
            return '<p>S3-Gallery-Konfiguration unvollständig.</p>';
        }

        require_once plugin_dir_path(__FILE__) . '/../includes/s3-client.php';
        $client = new S3Client($config);
        $prefix = trim($atts['folder']);

        try {
            $imageUrls = $client->list_objects_in_folder($config['bucket'], $prefix);
        } catch (Exception $e) {
            return '<div class="s3-gallery-error">Bilder konnten nicht geladen werden. Bitte überprüfen Sie Ihre Zugangsdaten.</div>';
        }

        ob_start();
        if (empty($imageUrls)) {
            echo '<p>Keine Bilder gefunden im Ordner: <code>' . esc_html($prefix) . '</code>.</p>';
        } else {
            echo '<div class="s3-gallery-grid" style="display: flex; flex-wrap: wrap; gap: 10px;">';
            foreach ($imageUrls as $img) {
                echo '<img src="' . esc_url($img) . '" style="width: 150px;" />';
            }
            echo '</div>';
        }

        return ob_get_clean();
    });
}
add_action('init', 's3_gallery_register_shortcode');
