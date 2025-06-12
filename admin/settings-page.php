<?php
function s3_gallery_register_settings() {
    add_options_page('S3 Galerie Einstellungen', 'S3 Galerie', 'manage_options', 's3-gallery', 's3_gallery_settings_page');

    register_setting('s3_gallery_options', 's3_gallery_endpoint');
    register_setting('s3_gallery_options', 's3_gallery_region');
    register_setting('s3_gallery_options', 's3_gallery_bucket');
    register_setting('s3_gallery_options', 's3_gallery_key');
    register_setting('s3_gallery_options', 's3_gallery_secret');
}
add_action('admin_menu', 's3_gallery_register_settings');

function s3_gallery_settings_page() {
    ?>
    <div class="wrap">
        <h1>S3 Galerie Einstellungen</h1>
        <form method="post" action="options.php">
            <?php settings_fields('s3_gallery_options'); ?>
            <table class="form-table">
                <tr><th>Endpoint URL</th><td><input type="text" name="s3_gallery_endpoint" value="<?= esc_attr(get_option('s3_gallery_endpoint')) ?>" placeholder="https://s3.eu-west-1.amazonaws.com oder https://s3.de.cloud.ovh.net"></td></tr>
                <tr><th>Region</th><td><input type="text" name="s3_gallery_region" value="<?= esc_attr(get_option('s3_gallery_region')) ?>" placeholder="z. B. eu-west-1 oder leer für OVH"></td></tr>
                <tr><th>Bucket</th><td><input type="text" name="s3_gallery_bucket" value="<?= esc_attr(get_option('s3_gallery_bucket')) ?>"></td></tr>
                <tr><th>Access Key</th><td><input type="text" name="s3_gallery_key" value="<?= esc_attr(get_option('s3_gallery_key')) ?>"></td></tr>
                <tr><th>Secret Key</th><td><input type="password" name="s3_gallery_secret" value="<?= esc_attr(get_option('s3_gallery_secret')) ?>"></td></tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
