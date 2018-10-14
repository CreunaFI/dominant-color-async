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

        $base_dir = wp_upload_dir()['basedir'];

        $dominant_color = null;

        if ($this->validate_medium_image_size($metadata)) {
            // We have medium image to work with
            $full_path = $base_dir . '/' . dirname($metadata['file']) . '/' . $metadata['sizes']['medium']['file'];
            $dominant_color = ColorThief::getColor($full_path, 5);
        } else {
            // We need to generate medium image

            $img = ImageManagerStatic::make($base_dir . '/' . $metadata['file']);
            if ($img->width() > $img->height()) {
                $img->widen(300, function ($constraint) {
                    $constraint->upsize();
                });
            } else {
                $img->heighten(300, function ($constraint) {
                    $constraint->upsize();
                });
            }
            // finally we save the image as a new file
            $img->save(plugin_dir_path(__FILE__) . '/bar.png');

            $dominant_color = ColorThief::getColor($img->getCore(), 5);
        }

        DominantColorAsync::debug($this->rgb_to_hex($dominant_color));

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

    public function rgb_to_hex($array)
    {
        return sprintf("#%02x%02x%02x", $array[0], $array[1], $array[2]);
    }
}
