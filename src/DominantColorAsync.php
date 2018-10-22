<?php

namespace DominantColorAsync;

class DominantColorAsync
{
    protected $process_all;
    private $plugin_basename;
    private $plugin_dir_path;

    public function __construct($plugin_basename, $plugin_dir_path)
    {
        $this->plugin_basename = $plugin_basename;
        $this->plugin_dir_path = $plugin_dir_path;

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
    }

    public function init()
    {
        $this->process_all = new Process();
    }

    public function media_fields($form_fields, $post)
    {

        $dominant_color = get_post_meta($post->ID, 'dominant_color', true) ?: null;

        $html = ' ';

        if ($dominant_color) {
            $html = '<div class="dominant-color-async">' .
                        '<div class="dominant-color-async-circle" style="background-color: ' . $dominant_color . '"></div>' .
                    '</div>';
        }

        $form_fields['dominant-color-async'] = [
            'label' => __('Dominant color', 'dominant-color-async'),
            'input' => 'html',
            'html' => $html,
        ];
        return $form_fields;
    }

    function load_admin_styles()
    {
        wp_enqueue_style('dominant-color-async', plugins_url('assets/dist/style.css', __DIR__), false, md5_file($this->plugin_dir_path . '/assets/dist/style.css'));
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
        echo 'Settings page';
    }
}
