<?php

namespace DominantColorAsync;

class DominantColorAsync
{
    protected $process_all;

    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'init']);
        add_action('init', [$this, 'process_handler']);
        add_filter(
            'wp_generate_attachment_metadata',
            [$this, 'add_image_to_queue'],
            10,
            2
        );
        add_filter('attachment_fields_to_edit', [$this, 'media_fields'], 10, 2);
    }

    public function init()
    {
        $this->process_all = new Process();
    }

    public function media_fields( $form_fields, $post ) {
        $dominant_color = null;
        if (get_post_meta($post->ID, 'dominant_color')) {
            $dominant_color = get_post_meta($post->ID, 'dominant_color', true);
        }
        $form_fields['dominant-color-async'] = [
            'label' => __('Dominant color', 'dominant-color-async'),
            'input' => 'html',
            'html' => '<p>' . $dominant_color .'</p>'
        ];
        return $form_fields;
    }

    public function process_handler()
    {
        if (!isset($_GET['process']) || !isset($_GET['_wpnonce'])) {
            return;
        }
        if (!wp_verify_nonce($_GET['_wpnonce'], 'process')) {
            return;
        }
        if ('all' === $_GET['process']) {
            $this->handle_all();
        }
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
}
