<?php

namespace DominantColorAsync;

use ColorThief\ColorThief;
use Intervention\Image\ImageManagerStatic;
use WP_Query;

class DominantColorAsync
{
    private $plugin_basename;
    private $plugin_dir_path;

    public function __construct($plugin_basename, $plugin_dir_path)
    {
        $this->plugin_basename = $plugin_basename;
        $this->plugin_dir_path = $plugin_dir_path;

        add_filter('dca_process_dominant_color', [$this, 'process_dominant_color'], 10, 2);
        add_filter('dca_process_transparency', [$this, 'process_transparency'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'load_admin_styles']);
        add_filter(
            'wp_generate_attachment_metadata',
            [$this, 'add_image_to_queue'],
            10,
            2
        );
        add_filter('attachment_fields_to_edit', [$this, 'media_fields'], 10, 2);

        // Add settings link on the plugin page
        add_filter('plugin_action_links_' . $this->plugin_basename, [$this, 'plugin_links']);

        add_action('admin_menu', function () {
            add_submenu_page(
                'upload.php',
                __('Dominant color async', 'dominant-color-async'),
                __('Dominant color async', 'dominant-color-async'),
                'manage_options',
                'dominant-color-async',
                [$this, 'settings_page']
            );
        });

        add_action('wp_ajax_dominant_color_status', [$this, 'check_status']);
        add_action('wp_ajax_dominant_color_process_all', [$this, 'process_all']);
    }

    public function media_fields($form_fields, $post)
    {

        $dominant_color = get_post_meta($post->ID, 'dominant_color', true) ?: null;

        $html = null;

        if ($dominant_color) {
            $html = '<div class="dominant-color-async">' .
                        '<div class="dominant-color-async-circle" style="background-color: ' . $dominant_color . '"></div>' .
                    '</div>';
        } else {
            $html = '<div class="dominant-color-async">' .
                '<a class="button button-small" href="' . admin_url('admin.php?page=dominant-color-async') .'">' . __('Calculate Missing Color', 'dominant-color-async') . '</a>' .
                '</div>';
        }

        $form_fields['dominant-color-async'] = [
            'label' => __('Dominant Color', 'dominant-color-async'),
            'input' => 'html',
            'html' => $html,
        ];
        return $form_fields;
    }

    function load_admin_styles($hook)
    {
        if (!empty($_GET['page']) && $_GET['page'] === 'dominant-color-async') {
            wp_enqueue_script("dominant-color-async-js", plugins_url('assets/dist/script.js', __DIR__), false, md5_file($this->plugin_dir_path . '/assets/dist/script.js'), true);
            wp_enqueue_style('dominant-color-async-css-dashboard', plugins_url('assets/dist/style-dashboard.css', __DIR__), false, md5_file($this->plugin_dir_path . '/assets/dist/style-dashboard.css'));
        }
        wp_enqueue_style('dominant-color-async-css', plugins_url('assets/dist/style.css', __DIR__), false, md5_file($this->plugin_dir_path . '/assets/dist/style.css'));
    }

    public function add_image_to_queue($metadata, $attachment_id)
    {
        if (!in_array(get_post_mime_type($attachment_id), ['image/jpeg', 'image/png', 'image/gif'])) {
            DominantColorAsync::debug('Attachment is not an image, skipping dominant color processing');
            return $metadata;
        }

        as_enqueue_async_action('dca_process_dominant_color', [$attachment_id]);
        as_enqueue_async_action('dca_process_transparency', [$attachment_id]);

        return $metadata;
    }

    static function debug($message)
    {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            error_log(print_r($message, true));
        }
    }

    public function process_dominant_color($attachment_id) {
        $this->process($attachment_id, 'dominant_color');
    }

    public function process_transparency($attachment_id) {
        $this->process($attachment_id, 'transparency');
    }

    /**
     * @param int $attachment_id
     * @param string $type
     * @return void
     */
    public function process($attachment_id, $type) {

        if (!get_post($attachment_id)) {
            DominantColorAsync::debug("Image $attachment_id does not exist, maybe it was deleted? Skipping.");
            return;
        }

        if (!in_array(get_post_mime_type($attachment_id), ['image/png', 'image/gif', 'image/jpeg'])) {
            return;
        }

        $metadata = wp_get_attachment_metadata($attachment_id);

        $base_dir = wp_upload_dir()['basedir'];

        $database_hash = get_post_meta($attachment_id, '_dca_hash', true);
        $file_path = $base_dir . '/' . $metadata['file'];

        if (
            $database_hash &&
            $database_hash === md5_file($file_path)
        ) {
            DominantColorAsync::debug("Image has not been changed");
            return;
        }

        if ($type === 'dominant_color') {
            DominantColorAsync::debug("Calculating dominant color...");
            $this->calculate_dominant_color($attachment_id, $metadata);
            DominantColorAsync::debug("Dominant color calculated!");
        }

        if ($type === 'transparency') {
            DominantColorAsync::debug("Calculating transparency...");
            $this->calculate_transparency($attachment_id, $metadata);
            update_post_meta($attachment_id, '_dca_hash', md5_file($file_path));
            DominantColorAsync::debug("Transparency calculated!");
        }
    }

    public function calculate_transparency($attachment_id, $metadata) {
        $has_transparency = $this->has_transparency($attachment_id, $metadata);
        update_post_meta($attachment_id, 'has_transparency', $has_transparency);
    }

    /**
     * Check whether image has transparency or not
     * @param int $attachment_id
     * @param array $metadata
     * @return bool
     */
    public function has_transparency($attachment_id, $metadata) {

        if (!in_array(get_post_mime_type($attachment_id), ['image/png', 'image/gif'])) {
            return false;
        }

        $base_dir = wp_upload_dir()['basedir'];
        $image = null;

        if ($this->validate_medium_image_size($metadata)) {
            // We have medium image to work with
            $full_path = $base_dir . '/' . dirname($metadata['file']) . '/' . $metadata['sizes']['medium']['file'];
            $image = ImageManagerStatic::make($full_path);
        } else {
            // We need to generate medium image
            $image_path = $base_dir . '/' . $metadata['file'];
            $image = $this->generate_thumbnail($image_path);
        }

        // Go through all pixels and if we find a transparent one, return true
        for ($y = 0; $y < $image->height(); $y++) {
            for ($x = 0; $x < $image->width(); $x++) {
                if ($image->pickColor($x, $y)[3] != 1) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Calculate dominant color and save it to attachment post meta
     * @param int $attachment_id
     * @param array $metadata
     */
    public function calculate_dominant_color($attachment_id, $metadata) {
        $base_dir = wp_upload_dir()['basedir'];

        $dominant_color = null;

        if ($this->validate_medium_image_size($metadata)) {
            // We have medium image to work with
            $full_path = $base_dir . '/' . dirname($metadata['file']) . '/' . $metadata['sizes']['medium']['file'];
            $dominant_color = ColorThief::getColor($full_path, 1);
        } else {
            // We need to generate medium image
            $image_path = $base_dir . '/' . $metadata['file'];
            $image = $this->generate_thumbnail($image_path);
            $dominant_color = ColorThief::getColor($image->getCore(), 1);
        }

        update_post_meta($attachment_id, 'dominant_color', $this->rgb_to_hex($dominant_color));
    }

    /**
     * Generate 300x300 thumbnail for image
     * @param string $image_path
     * @return \Intervention\Image\Image
     */
    public function generate_thumbnail($image_path) {
        $image = ImageManagerStatic::make($image_path);
        if ($image->width() > $image->height()) {
            $image->widen(300, function ($constraint) {
                $constraint->upsize();
            });
        } else {
            $image->heighten(300, function ($constraint) {
                $constraint->upsize();
            });
        }
        return $image;
    }

    /**
     * Make sure that medium image exists in WP, its size is 300x300 px and it exists in metadata
     * @param array $metadata
     * @return bool
     */
    public function validate_medium_image_size($metadata)
    {
        $sizes = get_intermediate_image_sizes();

        // Medium size exists
        if (!in_array('medium', $sizes)) {
            return false;
        }
        $width = (int)get_option("medium_size_w");
        $height = (int)get_option('medium_size_h');
        $crop = (bool)get_option('medium_crop');

        // Medium size equals 300x300 cropped and metadata contains medium
        if (
            $width === 300 &&
            $height === 300 &&
            $crop === false &&
            !empty($metadata['sizes']) &&
            !empty($metadata['sizes']['medium'])
        ) {
            return true;
        }
        return false;
    }

    public function plugin_links($links)
    {
        $settings_link = "<a href=\"upload.php?page=dominant-color-async\">" . __('Settings', 'dominant-color-async') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Render WordPress plugin settings page
     */
    public function settings_page()
    {
        $translations = [
            'unprocessed_images_notice' => __('There are %d images that don’t have color dominance information. Would you like to process them now?', 'dominant-color-async'),
            'process' => __('Process', 'dominant-color-async'),
            'dominant_color_async' => __('Dominant color async', 'dominant-color-async'),
            'processing_queue' => __('Processing queue', 'dominant-color-async'),
            'not_in_progress' => __('Not in progress', 'dominant-color-async'),
            'processing' => __('Processing', 'dominant-color-async'),
            'processed_images_count' => __('Processed %d out of %d images', 'dominant-color-async'),
        ];
        $translations = htmlspecialchars(json_encode($translations));

        echo '<div id="dominant-color-app" data-translations="' . $translations . '">';
        echo '        <dominant-color-app></dominant-color-app>';
        echo '      </div>';
    }

    public function check_status() {
        return false;
    }
    public function process_all()
    {
        return false;
    }

    public function rgb_to_hex($array)
    {
        return sprintf("#%02x%02x%02x", $array[0], $array[1], $array[2]);
    }
}
