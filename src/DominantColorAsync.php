<?php

namespace DominantColorAsync;

use WP_Query;

class DominantColorAsync
{
    protected $process_all;
    private $plugin_basename;
    private $plugin_dir_path;

    public function __construct($plugin_basename, $plugin_dir_path)
    {
        $this->plugin_basename = $plugin_basename;
        $this->plugin_dir_path = $plugin_dir_path;

        add_action('init', function () {
            load_plugin_textdomain('dominant-color-async', null, basename($this->plugin_dir_path) . '/languages');
        });

        add_action('plugins_loaded', [$this, 'init']);
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
                null,
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

    public function init()
    {
        $this->process_all = new Process();
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
        }
        wp_enqueue_style('dominant-color-async-css', plugins_url('assets/dist/style.css', __DIR__), false, md5_file($this->plugin_dir_path . '/assets/dist/style.css'));
    }

    public function add_image_to_queue($metadata, $attachment_id)
    {
        if (!in_array(get_post_mime_type($attachment_id), ['image/jpeg', 'image/png', 'image/gif'])) {
            DominantColorAsync::debug('Attachment is not an image, skipping dominant color processing');
            return $metadata;
        }
        $this->process_all->push_to_queue([
            'type' => 'dominant_color',
            'attachment_id' => $attachment_id,
            'metadata' => $metadata,
        ]);
        $this->process_all->push_to_queue([
            'type' => 'transparency',
            'attachment_id' => $attachment_id,
            'metadata' => $metadata,
        ]);

        $this->process_all->save()->dispatch();
        return $metadata;
    }

    static function debug($message)
    {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            error_log(print_r($message, true));
        }
    }

    public function plugin_links($links)
    {
        $settings_link = "<a href=\"options-general.php?page=dominant-color-async\">" . __('Settings', 'dominant-color-async') . '</a>';
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
        $in_progress = !$this->process_all->is_queue_empty() || $this->process_all->is_process_running();
        $total_query = new WP_Query([
            'post_status' => 'inherit',
            'post_type'=> 'attachment',
            'posts_per_page' => -1,
            'post_mime_type' => 'image/jpeg, image/gif, image/png'
        ]);
        $total = $total_query->post_count;

        $processed_query = new WP_Query([
            'post_status' => 'inherit',
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_mime_type' => 'image/jpeg, image/gif, image/png',
            'meta_query' => [
                [
                    'key' => 'dominant_color',
                    'compare' => 'EXISTS',
                ],
                [
                    'key' => 'has_transparency',
                    'compare' => 'EXISTS',
                ],
            ],
        ]);
        $processed = $processed_query->post_count;
        wp_send_json([
            'in_progress' => $in_progress,
            'total' => $total,
            'processed_images' => $processed,
            'unprocessed_images' => $total - $processed,
        ]);
        wp_die();
    }
    public function process_all()
    {
        $unprocessed_query = new WP_Query([
            'post_status' => 'inherit',
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_mime_type' => 'image/jpeg, image/gif, image/png',
            'meta_query' => 
            [
                [
                    'key' => 'dominant_color',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => 'has_transparency',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ]);
        foreach ($unprocessed_query->posts as $post) {
            $this->process_all->push_to_queue([
                'type' => 'dominant_color',
                'attachment_id' => $post->ID,
                'metadata' => wp_get_attachment_metadata($post->ID),
            ]);
            $this->process_all->push_to_queue([
                'type' => 'transparency',
                'attachment_id' => $post->ID,
                'metadata' => wp_get_attachment_metadata($post->ID),
            ]);
        }
        $this->process_all->save()->dispatch();
    }
}
