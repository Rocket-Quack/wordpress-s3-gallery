<?php
use Aws\S3\S3Client;
require_once __DIR__ . '/../vendor/autoload.php';

function s3_get_client() {
    $key    = get_option('s3_gallery_key');
    $secret = get_option('s3_gallery_secret');
    $region = get_option('s3_gallery_region');
    $endpoint = get_option('s3_gallery_endpoint');

    $config = [
        'version' => 'latest',
        'region' => $region ?: 'auto',
        'credentials' => [
            'key'    => $key,
            'secret' => $secret,
        ],
        'use_path_style_endpoint' => true
    ];

    if ($endpoint) {
        $config['endpoint'] = $endpoint;
    }

    return new S3Client($config);
}

function s3_list_folders() {
    $bucket = get_option('s3_gallery_bucket');
    $s3 = s3_get_client();

    $result = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Delimiter' => '/',
    ]);

    $folders = [];
    if (!empty($result['CommonPrefixes'])) {
        foreach ($result['CommonPrefixes'] as $prefix) {
            $folders[] = rtrim($prefix['Prefix'], '/');
        }
    }
    return $folders;
}

function s3_list_images($folder) {
    $bucket = get_option('s3_gallery_bucket');
    $s3 = s3_get_client();

    $result = $s3->listObjectsV2([
        'Bucket' => $bucket,
        'Prefix' => $folder . '/',
    ]);

    $images = [];
    foreach ($result['Contents'] as $object) {
        if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $object['Key'])) {
            $images[] = $s3->getObjectUrl($bucket, $object['Key']);
        }
    }
    return $images;
}

// AJAX
add_action('wp_ajax_get_s3_images', 's3_ajax_list_images');
add_action('wp_ajax_nopriv_get_s3_images', 's3_ajax_list_images');
function s3_ajax_list_images() {
    $folder = sanitize_text_field($_POST['folder']);
    wp_send_json(s3_list_images($folder));
}
