<?php
if (!defined('ABSPATH')) exit;

// Einstellungen registrieren
function s3_gallery_register_settings() {
    add_options_page(
        'S3 Galerie Einstellungen',
        'S3 Galerie',
        'manage_options',
        's3_gallery_settings',
        's3_gallery_settings_page'
    );

    register_setting('s3_gallery_options', 's3_gallery_endpoint');
    register_setting('s3_gallery_options', 's3_gallery_region');
    register_setting('s3_gallery_options', 's3_gallery_bucket');
    register_setting('s3_gallery_options', 's3_gallery_key');
    register_setting('s3_gallery_options', 's3_gallery_secret');
}
add_action('admin_menu', 's3_gallery_register_settings');

// Einstellungsseite mit Tabs
function s3_gallery_settings_page() {
    $active_tab = $_GET['tab'] ?? 'general';
    ?>

    <div class="wrap">
        <h1>S3 Galerie Einstellungen</h1>

        <h2 class="nav-tab-wrapper">
            <a href="?page=s3_gallery_settings&tab=general" class="nav-tab <?php echo ($active_tab == 'general') ? 'nav-tab-active' : ''; ?>">Allgemein</a>
            <a href="?page=s3_gallery_settings&tab=shortcodes" class="nav-tab <?php echo ($active_tab == 'shortcodes') ? 'nav-tab-active' : ''; ?>">Shortcodes</a>
        </h2>

        <?php
        if ($active_tab === 'shortcodes') {
            include plugin_dir_path(__FILE__) . 'shortcodes-tab.php';
        } else {
            ?>
            <form method="post" action="options.php">
                <?php settings_fields('s3_gallery_options'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Endpoint URL</th>
                        <td><input type="text" name="s3_gallery_endpoint" value="<?php echo esc_attr(get_option('s3_gallery_endpoint')); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Region</th>
                        <td><input type="text" name="s3_gallery_region" value="<?php echo esc_attr(get_option('s3_gallery_region')); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Bucket</th>
                        <td><input type="text" name="s3_gallery_bucket" value="<?php echo esc_attr(get_option('s3_gallery_bucket')); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Access Key</th>
                        <td><input type="text" name="s3_gallery_key" value="<?php echo esc_attr(get_option('s3_gallery_key')); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Secret Key</th>
                        <td><input type="password" name="s3_gallery_secret" value="<?php echo esc_attr(get_option('s3_gallery_secret')); ?>" class="regular-text" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <?php
        }
        ?>
    </div>
    <?php
}
