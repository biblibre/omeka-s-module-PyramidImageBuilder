<?php

namespace PyramidImageBuilder;

return [
    'controllers' => [
        'invokables' => [
            'PyramidImageBuilder\Controller\Admin\Index' => Controller\Admin\IndexController::class,
        ],
    ],
    'navigation' => [
        'AdminModule' => [
            [
                'label' => 'Pyramid Image Builder',
                'route' => 'admin/pyramid-image-builder',
                'resource' => 'PyramidImageBuilder\Controller\Admin\Index',
                'privilege' => 'index',
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'pyramid-image-builder' => [
                        'type' => \Laminas\Router\Http\Literal::class,
                        'options' => [
                            'route' => '/pyramid-image-builder',
                            'defaults' => [
                                '__NAMESPACE__' => 'PyramidImageBuilder\Controller\Admin',
                                'controller' => 'Index',
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'invokables' => [
            'PyramidImageBuilder\BuildStrategy\JPEG2000\ImageMagick' => BuildStrategy\JPEG2000\ImageMagick::class,
            'PyramidImageBuilder\BuildStrategy\TIFF\Vips' => BuildStrategy\TIFF\Vips::class,
            'PyramidImageBuilder\BuildStrategy\TIFF\ImageMagick' => BuildStrategy\TIFF\ImageMagick::class,
        ],
        'factories' => [
            'PyramidImageBuilder\Builder' => Service\BuilderFactory::class,
        ],
        'aliases' => [
            'PyramidImageBuilder\BuildStrategy' => 'PyramidImageBuilder\BuildStrategy\TIFF\ImageMagick',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
];
