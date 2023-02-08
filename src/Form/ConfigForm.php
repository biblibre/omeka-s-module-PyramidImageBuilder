<?php

namespace PyramidImageBuilder\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;

class ConfigForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'build_when_ingested',
            'type' => Checkbox::class,
            'options' => [
                'label' => 'Automatically build pyramid images', // @translate
                'info' => 'If enabled, adding an image file to Omeka will automatically trigger the build of a pyramid image', // @translate
            ],
        ]);

        $this->add([
            'name' => 'build_in_background_job',
            'type' => Checkbox::class,
            'options' => [
                'label' => 'Build images in a background job', // @translate
                'info' => 'Building a pyramid image can take a long time, slowing down media creation. Enable this option to build the pyramid image in a background job', // @translate
            ],
        ]);

        $this->add([
            'name' => 'tile_size',
            'type' => Select::class,
            'options' => [
                'label' => 'Tile size', // @translate
                'info' => 'Size of tiles in pixels', // @translate
                'value_options' => [
                    '128' => '128',
                    '256' => '256',
                    '512' => '512',
                    '1024' => '1024',
                ],
            ],
        ]);

        $this->add([
            'name' => 'media_types_whitelist',
            'type' => Text::class,
            'options' => [
                'label' => 'Media types whitelist', // @translate
                'info' => 'Comma-separated list of media types. Only the media types listed here will be considered for building a pyramid image', // @translate
            ],
        ]);

        $this->add([
            'name' => 'file_size_min',
            'type' => Text::class,
            'options' => [
                'label' => 'Minimum file size', // @translate
                'info' => 'Minimum file size in bytes. Only files bigger than this value will be considered for building a pyramid image', // @translate
            ],
        ]);

        $this->add([
            'name' => 'media_types_force',
            'type' => Text::class,
            'options' => [
                'label' => 'Media types always built', // @translate
                'info' => 'Comma-separated list of media types. Media types listed here will always be considered for building a pyramid image, ignoring other rules (like file size)', // @translate
            ],
        ]);
    }
}
