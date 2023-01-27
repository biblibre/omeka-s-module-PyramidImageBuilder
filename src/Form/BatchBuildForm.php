<?php

namespace PyramidImageBuilder\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\Checkbox;

class BatchBuildForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'overwrite',
            'type' => Checkbox::class,
            'options' => [
                'label' => 'Overwrite existing pyramid images', // @translate
            ],
        ]);
    }
}
