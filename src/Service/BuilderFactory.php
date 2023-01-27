<?php

namespace PyramidImageBuilder\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use PyramidImageBuilder\Builder;

class BuilderFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $fileStore = $serviceLocator->get('Omeka\File\Store');
        $buildStrategy = $serviceLocator->get('PyramidImageBuilder\BuildStrategy');

        return new Builder($fileStore, $buildStrategy);
    }
}
