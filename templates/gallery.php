<?php
function s3_gallery_register_shortcode() {
    add_shortcode('s3_gallery', function() {
        $config = [
            'endpoint' => get_option('s3_gallery_endpoint'),
            'region' => get_option('s3_gallery_region'),
            'bucket' => get_option('s3_gallery_bucket'),
            'access_key' => get_option('s3_gallery_key'),
            'secret_key' => get_option('s3_gallery_secret'),
        ];

        $client = new S3Client($config);
        $objects = $client->listObjects();

        ob_start();
        echo '<div class="s3-gallery">';
        foreach ($objects as $object) {
            $url = rtrim($config['endpoint'], '/') . '/' . $config['bucket'] . '/' . $object;
            echo '<img src="' . esc_url($url) . '" style="max-width:200px; margin:10px;">';
        }
        echo '</div>';
        return ob_get_clean();
    });
}
add_action('init', 's3_gallery_register_shortcode');
