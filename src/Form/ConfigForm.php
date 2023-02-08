<?php

namespace PyramidImageBuilder\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Select;

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
    }
}
