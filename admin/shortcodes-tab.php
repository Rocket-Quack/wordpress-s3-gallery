<?php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '/../includes/s3-client.php';

$bucket = get_option('s3_gallery_bucket');
$directories = [];

if ($bucket) {
    try {
        $config = [
            'endpoint'   => get_option('s3_gallery_endpoint'),
            'region'     => get_option('s3_gallery_region'),
            'bucket'     => get_option('s3_gallery_bucket'),
            'access_key' => get_option('s3_gallery_key'),
            'secret_key' => get_option('s3_gallery_secret'),
        ];

        $client = new S3Client($config);
        $directories = $client->list_directories($bucket);
    } catch (Exception $e) {
        echo '<div class="notice notice-error"><p>Fehler beim Abrufen der Verzeichnisse: ' . esc_html($e->getMessage()) . '</p></div>';
    }
} else {
    echo '<div class="notice notice-warning"><p>Bitte zuerst einen Bucket in den Einstellungen angeben.</p></div>';
}
?>

<h2>Verfügbare Shortcodes</h2>
<p>Gefundene Verzeichnisse im Bucket <code><?php echo esc_html($bucket); ?></code>:</p>

<?php if (!empty($directories)) : ?>
    <table class="widefat striped">
        <thead>
            <tr>
                <th>Ordner</th>
                <th>Shortcode</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($directories as $dir) : ?>
                <tr>
                    <td><?php echo esc_html($dir); ?></td>
                    <td><code>[s3_gallery folder="<?php echo esc_attr($dir); ?>"]</code></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>
    <p>Keine Ordner gefunden oder leerer Bucket.</p>
<?php endif; ?>
