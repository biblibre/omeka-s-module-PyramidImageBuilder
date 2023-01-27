<?php

namespace PyramidImageBuilder\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\Checkbox;

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
    }
}
