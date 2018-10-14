<?php

namespace DominantColorAsync;

use ColorThief\ColorThief;
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

        if ($this->validate_medium_image_size($metadata)) {
            // We have medium image to work with
            $full_path = wp_upload_dir()['basedir'] . '/' . dirname($metadata['file']) . '/' . $metadata['sizes']['medium']['file'];
            $color_thief = ColorThief::getColor($full_path);
            DominantColorAsync::debug($this->rgb_to_hex($color_thief));
        } else {
            // We need to generate medium image
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

    public function validate_medium_image_size($metadata) {
        $sizes = get_intermediate_image_sizes();
        // Medium size exists
        if (!collect($sizes)->contains('medium')) {
            return false;
        }
        $width = (int)get_option("medium_size_w");
        $height = (int)get_option('medium_size_h');
        $crop = (bool)get_option('medium_crop');
        // Medium size equals 300x300 cropped and metadata contains medium
        if ($width == 300 && $height === 300 && $crop === false && $metadata['sizes'] && $metadata['sizes']['medium']) {
            return true;
        }
        return false;
    }

    public function rgb_to_hex($array) {
        return sprintf("#%02x%02x%02x", $array[0], $array[1], $array[2]);
    }
}
