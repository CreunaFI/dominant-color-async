<?php

namespace DominantColorAsync;

use ColorThief\ColorThief;
use Intervention\Image\ImageManagerStatic;
use WP_Background_Process;

class Process extends WP_Background_Process
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @var string
     */
    protected $action = 'dominant_color_async';

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param mixed $data Queue item to iterate over
     *
     * @return mixed
     */
    protected function task($data)
    {
        $attachment_id = $data['attachment_id'];
        $metadata = $data['metadata'];
        $type = $data['type'];

        if ($type === 'dominant_color') {
            $this->calculate_dominant_color($attachment_id, $metadata);
        }
        if ($type === 'transparency') {
            $this->calculate_transparency($attachment_id, $metadata);
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
            ImageManagerStatic::configure([
                'driver' => extension_loaded('imagick') ? 'imagick' : 'gd',
            ]);
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
     * Complete
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     */
    protected function complete()
    {
        parent::complete();
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
        if (!collect($sizes)->contains('medium')) {
            return false;
        }
        $width = (int)get_option("medium_size_w");
        $height = (int)get_option('medium_size_h');
        $crop = (bool)get_option('medium_crop');

        // Medium size equals 300x300 cropped and metadata contains medium
        if ($width === 300 && $height === 300 && $crop === false && $metadata['sizes'] && $metadata['sizes']['medium']) {
            return true;
        }
        return false;
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

    public function rgb_to_hex($array)
    {
        return sprintf("#%02x%02x%02x", $array[0], $array[1], $array[2]);
    }

    /**
     * Expose protected method
     * @return bool
     */
    public function is_queue_empty()
    {
        return parent::is_queue_empty();
    }

    /**
     * Expose protected method
     * @return bool
     */
    public function is_process_running()
    {
        return parent::is_process_running();
    }
}
